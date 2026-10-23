<?php

namespace App\Http\Controllers;

use App\Events\PesananBaru;
use App\Events\StatusPesananBerubah;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Services\PesananService;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                'name' => 'SAUNG '.$m->kode_meja,
                'tableName' => $m->area.' '.$m->kode_meja,
                'capacity' => $m->area.' Kap. '.$m->kapasitas.' org',
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

            $statusMap = [
                Pesanan::STATUS_MENUNGGU => 'waiting_cash',
                Pesanan::STATUS_MENUNGGU_VERIFIKASI => 'waiting_qris',
                Pesanan::STATUS_MENUNGGU_PEMBAYARAN => 'waiting_cash',
                Pesanan::STATUS_DIPROSES => 'preparing',
                Pesanan::STATUS_SIAP => 'ready',
                Pesanan::STATUS_DIANTAR => 'delivering',
                Pesanan::STATUS_SELESAI => 'completed',
                Pesanan::STATUS_DIBATALKAN => 'cancelled',
            ];

            $alpineStatus = $statusMap[$p->status_pesanan] ?? 'empty';

            // Bug C FIX: Jika pesanan sudah dibayar (status_pembayaran = sudah_bayar)
            // dan bukan lagi menunggu pembayaran, kategorikan sebagai 'paid' di kasir
            // agar saung masuk ke filter "Sudah Lunas" dan panel kanan ter-lock.
            $sudahBayar = $p->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR;
            $masihMenunggu = in_array($p->status_pesanan, [
                Pesanan::STATUS_MENUNGGU,
                Pesanan::STATUS_MENUNGGU_PEMBAYARAN,
                Pesanan::STATUS_MENUNGGU_VERIFIKASI,
            ]);
            if ($sudahBayar && ! $masihMenunggu) {
                $alpineStatus = 'paid';
            }

            $dasar['status'] = $alpineStatus;
            $dasar['paymentType'] = $p->metode_pembayaran;
            $dasar['paymentStatus'] = $p->status_pembayaran;
            $dasar['orderStatus'] = $p->status_pesanan;
            $dasar['orderId'] = $p->kode_pesanan;
            $dasar['kodePesanan'] = $p->kode_pesanan;
            $dasar['customer'] = 'Tamu '.$m->kode_meja;
            $dasar['qrTime'] = $p->waktu_pesan->format('H:i');
            $dasar['elapsedTime'] = now()->diffInMinutes($p->waktu_pesan).' mnt lalu';
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
                    'note' => $d->catatan ? 'Catatan: '.$d->catatan : 'Tanpa catatan',
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
                'title' => 'PANGGILAN PELAYAN AKTIF: SAUNG '.$panggil->meja->kode_meja.' ('.$panggil->meja->area.')',
                'subtitle' => 'Panggilan masuk ('.$panggil->waktu_panggil->format('H:i:s').') Tamu butuh bantuan pelayan',
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

        $statusPriority = [
            'waiting_qris' => 1,
            'waiting_cash' => 2,
            'preparing' => 3,
            'ready' => 4,
            'delivering' => 5,
            'completed' => 6,
            'cancelled' => 7,
            'empty' => 8,
        ];

        $pertama = $saungsJson->sortBy(fn ($s) => $statusPriority[$s['status']] ?? 99)->first();
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

        try {
            $pesanan = app(PesananService::class)->buat(
                $meja,
                $dipilih->all(),
                [],
                [
                    'asal' => 'kasir',
                    'metode' => $data['metode_pembayaran'] ?? 'tunai',
                    'kasir_id' => $request->user()->id,
                    'catatan' => $data['catatan'] ?? null,
                ]
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['jumlah' => $e->getMessage()])->withInput();
        }

        Realtime::emit(new PesananBaru([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $meja->kode_meja,
            'meja_id' => $meja->id,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
            'total_bayar' => (float) $pesanan->total_bayar,
        ]));

        return redirect()->route('kasir.pembayaran.create', $pesanan->kode_pesanan);
    }

    public function show(string $kode): View
    {
        $pesanan = Pesanan::with(['detailPesanan.menu', 'meja', 'pembayaran'])
            ->where('kode_pesanan', $kode)
            ->firstOrFail();

        return view('pesanan.show', compact('pesanan'));
    }

    public function ubahStatus(Request $request, string $kode): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'status_pesanan' => ['required', 'in:menunggu,diproses,siap,diantar,selesai,dibatalkan'],
            'alasan' => ['nullable', 'string', 'max:255'],
        ]);

        $pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();

        // Bug 3 FIX: Tambahkan semua status yang didefinisikan di model
        // Status menunggu_pembayaran & menunggu_verifikasi berasal dari e-menu checkout
        // Tanpa ini, PATCH ke /pesanan/{kode}/status crash dengan undefined array key
        $alur = [
            'menunggu' => ['diproses', 'dibatalkan'],
            'menunggu_pembayaran' => ['diproses', 'dibatalkan'],
            'menunggu_verifikasi' => ['diproses', 'dibatalkan'],
            'diproses' => ['siap', 'dibatalkan'],
            'siap' => ['diantar', 'dibatalkan'],
            'diantar' => ['selesai', 'dibatalkan'],
            'selesai' => [],
            'dibatalkan' => [],
        ];

        if (! in_array($data['status_pesanan'], $alur[$pesanan->status_pesanan])) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Perpindahan status tidak diizinkan.'], 422);
            }

            return back()->withErrors(['status_pesanan' => 'Perpindahan status tidak diizinkan.']);
        }

        if ($data['status_pesanan'] === 'dibatalkan') {
            app(PesananService::class)->batalkan(
                $pesanan,
                $request->user()->id,
                $data['alasan'] ?? 'Dibatalkan oleh kasir.'
            );
        } else {
            $pesanan->update(['status_pesanan' => $data['status_pesanan']]);
        }

        $pesanan->refresh();

        Realtime::emit(new StatusPesananBerubah([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $pesanan->meja->kode_meja ?? null,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
        ]));
        // Bug 2 FIX: Return JSON untuk AJAX request (fetch dari Alpine tab-antar)
        // Return redirect untuk form biasa (kasir dashboard)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Status pesanan diperbarui',
                'pesanan' => [
                    'kode_pesanan' => $pesanan->kode_pesanan,
                    'status_pesanan' => $data['status_pesanan'],
                ],
            ]);
        }

        return back();
    }
}
