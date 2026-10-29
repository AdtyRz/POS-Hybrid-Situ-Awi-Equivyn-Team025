<?php

namespace App\Http\Controllers;

use App\Events\PanggilanPelayan;
use App\Events\StatusPesananBerubah;
use App\Models\DetailPesanan;
use App\Models\LogPanggilPelayan;
use App\Models\Pesanan;
use App\Services\ShiftService;
use App\Support\Realtime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KdsController extends Controller
{
    public function index(Request $request): View
    {
        $target = $request->route('target', 'dapur');

        if ($target === 'bar') {
            $judul = 'Bar';
            $subJudul = 'Beverage Display System';
            $lencana = 'KDS BAR — MINUMAN & KOPI';
            $tampilan = 'BARISTA VIEW';
            $kataMulai = 'Racik';
            $kataSiap = 'Minuman';
            $kataRunner = 'Bar';
            $stasiunNama = 'Bar & Racikan';
            $stasiunSub = 'STATION POS 02 • KHUSUS MINUMAN';
            $stasiunUtama = 'Bar Utama';
            $stasiun = 'Bar';
        } else {
            $target = 'dapur';
            $judul = 'Dapur';
            $subJudul = 'Kitchen Display System';
            $lencana = 'KDS DAPUR — MAKANAN & BAKARAN';
            $tampilan = 'KOKI VIEW';
            $kataMulai = 'Masak';
            $kataSiap = 'Makanan';
            $kataRunner = 'Dapur';
            $stasiunNama = 'Hot Kitchen & Bakaran';
            $stasiunSub = 'STATION POS 01 • KHUSUS MAKANAN';
            $stasiunUtama = 'Dapur Utama';
            $stasiun = 'Dapur';
        }

        $tiket = DetailPesanan::with(['menu', 'pesanan.meja'])
            ->whereHas('menu.kategori', function ($query) use ($target) {
                $query->where('target_kds', $target);
            })
            ->whereIn('status_item', ['menunggu', 'diproses', 'siap'])
            ->whereHas('pesanan', function ($query) {
                // Tampilkan tiket selama pesanan masih dalam alur produksi:
                // 'menunggu' = baru masuk, 'diproses' = sedang dimasak, 'siap' = selesai tapi runner belum ambil.
                // Tiket baru hilang dari KDS saat status berubah ke 'diantar' atau 'selesai'.
                $query->whereIn('status_pesanan', ['menunggu', 'diproses', 'siap']);
            })
            ->orderBy('created_at')
            ->get()
            ->groupBy('pesanan_id');

        $tiketJson = $tiket->map(function ($baris, $pesananId) {
            $pesanan = $baris->first()->pesanan;
            $status = $this->statusTiket($baris);

            // Bug FIX: Timer harus berhenti saat tiket status "siap".
            // Untuk tiket "siap", hitung detik dari waktu_pesan → max(updated_at) item (waktu selesai masak).
            // Untuk tiket lain, hitung dari waktu_pesan → sekarang (timer masih berjalan).
            if ($status === 'siap') {
                $waktuSelesai = $baris->max('updated_at');
                $detik = $waktuSelesai
                    ? max(0, (int) $pesanan->waktu_pesan->diffInSeconds($waktuSelesai))
                    : 0;
            } else {
                $detik = (int) now()->diffInSeconds($pesanan->waktu_pesan);
            }

            return [
                'dbId' => $pesanan->id,
                'id' => '#' . $pesanan->kode_pesanan,
                'saung' => 'SAUNG ' . $pesanan->meja->kode_meja,
                'customer' => $pesanan->nama_pelanggan ?: 'Tamu ' . $pesanan->meja->kode_meja,
                'status' => $status,
                'seconds' => $detik,
                'frozen' => $status === 'siap',
                'isOverdue' => $detik >= 900 && $status !== 'siap',
                'passBarMessage' => 'Di Meja Pass Bar: Menunggu pelayan mengantar ke Saung ' . $pesanan->meja->kode_meja . '.',
                'items' => $baris->map(function ($detail) {
                    return [
                        'dbId' => $detail->id,
                        'name' => $detail->menu->nama_menu,
                        'qty' => $detail->jumlah . 'x',
                        'checked' => $detail->status_item !== 'menunggu',
                        'note' => $detail->catatan ? 'Catatan: ' . $detail->catatan : null,
                        'noteType' => $detail->catatan ? (str_contains(strtolower($detail->catatan), 'pedas') ? 'warning' : 'info') : null,
                    ];
                })->values(),
            ];
        })->values();

        $selesaiHariIni = DetailPesanan::where('status_item', 'siap')
            ->whereDate('updated_at', today())
            ->whereHas('menu.kategori', function ($query) use ($target) {
                $query->where('target_kds', $target);
            })
            ->sum('jumlah');

        // Bug FIX: Hitung rata-rata waktu masak dari database (bukan hardcode "14 Mnt").
        // Ambil semua pesanan yang punya item "siap" hari ini di stasiun ini,
        // lalu hitung selisih waktu_pesan → max(updated_at) per pesanan.
        $pesananSelesaiHariIni = DetailPesanan::with('pesanan')
            ->whereHas('menu.kategori', function ($query) use ($target) {
                $query->where('target_kds', $target);
            })
            ->where('status_item', 'siap')
            ->whereDate('updated_at', today())
            ->get()
            ->groupBy('pesanan_id');

        $durasiMasakList = $pesananSelesaiHariIni->map(function ($items) {
            $pesanan = $items->first()->pesanan;
            if (! $pesanan || ! $pesanan->waktu_pesan || ! $pesanan->waktu_pesan->isToday()) {
                return null;
            }
            $waktuSelesai = $items->max('updated_at');
            if (! $waktuSelesai) {
                return null;
            }
            return max(0, (int) $pesanan->waktu_pesan->diffInSeconds($waktuSelesai));
        })->filter(fn ($d) => $d !== null && $d > 0)->values();

        $rataRataMasakMenit = $durasiMasakList->isNotEmpty()
            ? max(1, (int) round($durasiMasakList->avg() / 60))
            : 0;

        $rataRataMasakTeks = $rataRataMasakMenit > 0 ? $rataRataMasakMenit . ' Mnt' : '— Mnt';
        $labelShift = ShiftService::ringkasan()['label'];

        return view('KDS.index', compact(
            'target', 'judul', 'subJudul', 'lencana', 'tampilan',
            'kataMulai', 'kataSiap', 'kataRunner',
            'stasiunNama', 'stasiunSub', 'stasiunUtama', 'stasiun',
            'tiketJson', 'selesaiHariIni', 'rataRataMasakTeks', 'labelShift'
        ));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $target = $request->route('target', 'dapur');

        $data = $request->validate([
            'aksi' => ['required', 'in:mulai,siap,item,runner'],
            'item_id' => ['nullable', 'exists:detail_pesanan,id'],
        ]);

        $panggilanRunner = null;
        $statusPayload = null;

        DB::transaction(function () use ($target, $id, $data, &$panggilanRunner, &$statusPayload) {
            $pesanan = Pesanan::findOrFail($id);

            if ($data['aksi'] === 'runner') {
                $panggilanRunner = LogPanggilPelayan::create([
                    'meja_id' => $pesanan->meja_id,
                    'status_panggilan' => 'menunggu',
                ]);
                return;
            }

            if ($data['aksi'] === 'item' && $data['item_id']) {
                $detail = DetailPesanan::where('id', $data['item_id'])
                    ->where('pesanan_id', $pesanan->id)
                    ->whereHas('menu.kategori', function ($query) use ($target) {
                        $query->where('target_kds', $target);
                    })
                    ->firstOrFail();

                $detail->update([
                    'status_item' => $detail->status_item === 'menunggu' ? 'diproses' : 'menunggu',
                ]);
            } else {
                $tujuan = $data['aksi'] === 'siap' ? 'siap' : 'diproses';

                DetailPesanan::where('pesanan_id', $pesanan->id)
                    ->whereHas('menu.kategori', function ($query) use ($target) {
                        $query->where('target_kds', $target);
                    })
                    ->update(['status_item' => $tujuan]);
            }

            $pesanan->refresh();
            $pesanan->update(['status_pesanan' => $this->statusPesanan($pesanan)]);

            $statusPayload = [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'meja' => $pesanan->meja->kode_meja ?? null,
                'status_pesanan' => $pesanan->status_pesanan,
                'status_pembayaran' => $pesanan->status_pembayaran,
                'metode_pembayaran' => $pesanan->metode_pembayaran,
            ];
        });

        if ($statusPayload) {
            Realtime::emit(new StatusPesananBerubah($statusPayload));
        }

        if ($panggilanRunner) {
            Realtime::emit(new PanggilanPelayan([
                'id' => $panggilanRunner->id,
                'meja_id' => $panggilanRunner->meja_id,
                'meja' => $panggilanRunner->meja->kode_meja ?? null,
                'area' => $panggilanRunner->meja->area ?? null,
                'status_panggilan' => 'menunggu',
            ]));
        }

        return back();
    }

    private function statusTiket($baris): string
    {
        if ($baris->every(fn ($detail) => $detail->status_item === 'siap')) {
            return 'siap';
        }

        if ($baris->contains(fn ($detail) => $detail->status_item !== 'menunggu')) {
            return 'diproses';
        }

        return 'menunggu';
    }

    private function statusPesanan(Pesanan $pesanan): string
    {
        // Bug 5 FIX: Cek status_item PER semua item di pesanan ini
        // Pesanan dianggap 'siap' jika SEMUA item (lintas KDS) sudah siap.
        // Pesanan 'diproses' jika ada item yang sudah mulai diproses.
        // Ini memastikan dapur dan bar harus sama-sama selesai sebelum pesanan muncul di tab pelayan.
        $allStatus = DetailPesanan::where('pesanan_id', $pesanan->id)
            ->pluck('status_item');

        if ($allStatus->every(fn ($item) => $item === 'siap')) {
            return 'siap';
        }

        if ($allStatus->contains(fn ($item) => $item !== 'menunggu')) {
            return 'diproses';
        }

        return 'menunggu';
    }
}
