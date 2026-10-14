<?php

namespace App\Http\Controllers;

use App\Events\PanggilanPelayan;
use App\Events\StatusPesananBerubah;
use App\Models\DetailPesanan;
use App\Models\LogPanggilPelayan;
use App\Models\Pesanan;
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
            $detik = now()->diffInSeconds($pesanan->waktu_pesan);

            return [
                'dbId' => $pesanan->id,
                'id' => '#' . $pesanan->kode_pesanan,
                'saung' => 'SAUNG ' . $pesanan->meja->kode_meja,
                'customer' => 'Tamu ' . $pesanan->meja->kode_meja,
                'status' => $status,
                'seconds' => $detik,
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

        return view('KDS.index', compact(
            'target', 'judul', 'subJudul', 'lencana', 'tampilan',
            'kataMulai', 'kataSiap', 'kataRunner',
            'stasiunNama', 'stasiunSub', 'stasiunUtama', 'stasiun',
            'tiketJson', 'selesaiHariIni'
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
