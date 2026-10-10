<?php

namespace App\Http\Controllers;

use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\StokMutasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function index(): View
    {
        $aktif = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->whereDate('waktu_pesan', today())
            ->whereNotIn('status_pesanan', ['selesai', 'dibatalkan'])
            ->orderBy('waktu_pesan')
            ->get()
            ->keyBy('meja_id');

        $mejaSemua = MejaMakan::orderBy('kode_meja')->get();

        $tunggu = LogPanggilPelayan::where('status_panggilan', 'menunggu')
            ->pluck('meja_id')
            ->toArray();

        $saungsJson = $mejaSemua->map(function ($m) use ($aktif, $tunggu) {
            $dasar = [
                'id' => $m->kode_meja,
                'dbId' => $m->id,
                'cluster' => explode('-', $m->kode_meja)[0],
                'name' => 'SAUNG ' . $m->kode_meja,
                'tableName' => $m->area . ' ' . $m->kode_meja,
                'capacity' => $m->area . ' Kap. ' . $m->kapasitas . ' org',
                'status' => 'empty',
                'paymentType' => null,
                'hasAlert' => in_array($m->id, $tunggu),
                'orderId' => '-',
                'kodePesanan' => null,
                'customer' => '-',
                'qrTime' => '-',
                'elapsedTime' => '-',
                'itemCount' => 0,
                'totalPortions' => 0,
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'items' => [],
            ];

            $p = $aktif->get($m->id);

            if (! $p) {
                return $dasar;
            }

            $subtotal = (float) $p->detailPesanan->sum('subtotal');
            $total = (float) $p->total_bayar;
            $pajak = $total - $subtotal;

            if ($pajak < 0) {
                $pajak = round($subtotal * 0.1);
            }

            $dasar['status'] = $p->status_pembayaran === 'sudah_bayar' ? 'paid' : 'waiting_cash';
            $dasar['paymentType'] = $p->metode_pembayaran;
            $dasar['orderId'] = $p->kode_pesanan;
            $dasar['kodePesanan'] = $p->kode_pesanan;
            $dasar['customer'] = 'Tamu ' . $m->kode_meja;
            $dasar['qrTime'] = $p->waktu_pesan->format('H:i');
            $dasar['elapsedTime'] = now()->diffInMinutes($p->waktu_pesan) . ' mnt lalu';
            $dasar['itemCount'] = (int) $p->detailPesanan->sum('jumlah');
            $dasar['totalPortions'] = (int) $p->detailPesanan->sum('jumlah');
            $dasar['subtotal'] = $subtotal;
            $dasar['tax'] = $pajak;
            $dasar['total'] = $total;
            $dasar['items'] = $p->detailPesanan->map(function ($d) {
                return [
                    'name' => $d->menu->nama_menu,
                    'qty' => (int) $d->jumlah,
                    'price' => (float) $d->harga_satuan,
                    'note' => $d->catatan ? 'Catatan: ' . $d->catatan : 'Tanpa catatan',
                    'noteColor' => 'text-[#6B6A63]',
                ];
            })->values();

            return $dasar;
        })->values();

        $panggil = LogPanggilPelayan::with('meja')
            ->where('status_panggilan', 'menunggu')
            ->orderBy('waktu_panggil')
            ->first();

        if ($panggil) {
            $iotAlertJson = [
                'active' => true,
                'id' => $panggil->id,
                'saungId' => $panggil->meja->kode_meja,
                'title' => 'PANGGILAN PELAYAN AKTIF: SAUNG ' . $panggil->meja->kode_meja . ' (' . $panggil->meja->area . ')',
                'subtitle' => 'Panggilan masuk (' . $panggil->waktu_panggil->format('H:i:s') . ') Tamu butuh bantuan pelayan',
            ];
        } else {
            $iotAlertJson = [
                'active' => false,
                'id' => null,
                'saungId' => null,
                'title' => '',
                'subtitle' => '',
            ];
        }

        $pertama = $saungsJson->firstWhere('status', 'waiting_cash') ?? $saungsJson->firstWhere('status', 'paid') ?? $saungsJson->first();
        $selectedSaungId = $pertama ? $pertama['id'] : null;

        return view('kasir.index', compact('saungsJson', 'iotAlertJson', 'selectedSaungId'));
    }

    public function create(): View
    {
        $meja = MejaMakan::where('status_meja', 'kosong')->orderBy('kode_meja')->get();
        $menu = Menu::where('stok_menu', '>', 0)->orderBy('nama_menu')->get();

        return view('kasir.tambah', compact('meja', 'menu'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'meja_id' => ['required', 'exists:meja_makan,id'],
            'metode_pembayaran' => ['nullable', 'in:tunai,qris'],
            'catatan' => ['nullable', 'string', 'max:255'],
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        $dipilih = collect($data['jumlah'])->filter(fn ($j) => $j > 0);

        if ($dipilih->isEmpty()) {
            return back()->withErrors(['jumlah' => 'Pilih minimal satu menu.'])->withInput();
        }

        $meja = MejaMakan::findOrFail($data['meja_id']);
        $daftarMenu = Menu::whereIn('id', $dipilih->keys())->get()->keyBy('id');

        foreach ($dipilih as $id => $jml) {
            if (! isset($daftarMenu[$id]) || $daftarMenu[$id]->stok_menu < $jml) {
                $nama = isset($daftarMenu[$id]) ? $daftarMenu[$id]->nama_menu : 'menu';
                return back()->withErrors(['jumlah' => 'Stok ' . $nama . ' tidak cukup.'])->withInput();
            }
        }

        $pesanan = DB::transaction(function () use ($data, $meja, $daftarMenu, $dipilih) {
            $subtotal = 0;

            foreach ($dipilih as $id => $jml) {
                $subtotal += $daftarMenu[$id]->harga * $jml;
            }

            $baru = Pesanan::create([
                'kode_pesanan' => 'SW-' . now()->format('YmdHis') . '-' . $meja->id,
                'meja_id' => $meja->id,
                'kasir_id' => auth()->id(),
                'total_bayar' => $subtotal + round($subtotal * 0.1),
                'status_pesanan' => 'menunggu',
                'status_pembayaran' => 'belum_bayar',
                'metode_pembayaran' => $data['metode_pembayaran'] ?? 'tunai',
                'catatan' => $data['catatan'] ?? null,
            ]);

            foreach ($dipilih as $id => $jml) {
                $m = $daftarMenu[$id];

                $baru->detailPesanan()->create([
                    'menu_id' => $m->id,
                    'jumlah' => $jml,
                    'harga_satuan' => $m->harga,
                    'subtotal' => $m->harga * $jml,
                    'status_item' => 'menunggu',
                ]);

                $m->decrement('stok_menu', $jml);

                StokMutasi::create([
                    'menu_id' => $m->id,
                    'pesanan_id' => $baru->id,
                    'jenis' => 'penjualan',
                    'jumlah_delta' => -$jml,
                    'stok_akhir' => $m->fresh()->stok_menu,
                    'created_by' => auth()->id(),
                ]);
            }

            $meja->update(['status_meja' => 'terisi']);

            return $baru;
        });

        return redirect()->route('kasir.pembayaran.create', $pesanan->kode_pesanan);
    }

    public function show(string $kode): View
    {
        $pesanan = Pesanan::with(['detailPesanan.menu', 'meja', 'pembayaran'])
            ->where('kode_pesanan', $kode)
            ->firstOrFail();

        return view('pesanan.show', compact('pesanan'));
    }

    public function ubahStatus(Request $request, string $kode): RedirectResponse
    {
        $data = $request->validate([
            'status_pesanan' => ['required', 'in:menunggu,diproses,siap,diantar,selesai,dibatalkan'],
        ]);

        $pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();

        $alur = [
            'menunggu' => ['diproses', 'dibatalkan'],
            'diproses' => ['siap', 'dibatalkan'],
            'siap' => ['diantar', 'dibatalkan'],
            'diantar' => ['selesai', 'dibatalkan'],
            'selesai' => [],
            'dibatalkan' => [],
        ];

        if (! in_array($data['status_pesanan'], $alur[$pesanan->status_pesanan])) {
            return back()->withErrors(['status_pesanan' => 'Perpindahan status tidak diizinkan.']);
        }

        DB::transaction(function () use ($pesanan, $data) {
            $isi = ['status_pesanan' => $data['status_pesanan']];

            if ($data['status_pesanan'] === 'dibatalkan') {
                $isi['void_by'] = auth()->id();
                $isi['void_at'] = now();
            }

            if (in_array($data['status_pesanan'], ['selesai', 'dibatalkan'])) {
                $pesanan->meja()->update(['status_meja' => 'kosong']);
            }

            $pesanan->update($isi);
        });

        return back();
    }
}
