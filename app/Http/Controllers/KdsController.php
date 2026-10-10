<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\LogPanggilPelayan;
use App\Models\Pesanan;
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
                $query->whereNotIn('status_pesanan', ['selesai', 'dibatalkan']);
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

        DB::transaction(function () use ($target, $id, $data) {
            $pesanan = Pesanan::findOrFail($id);

            if ($data['aksi'] === 'runner') {
                LogPanggilPelayan::create([
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

            $pesanan->update(['status_pesanan' => $this->statusPesanan($pesanan)]);
        });

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
        $status = DetailPesanan::where('pesanan_id', $pesanan->id)->pluck('status_item');

        if ($status->every(fn ($item) => $item === 'siap')) {
            return 'siap';
        }

        if ($status->contains(fn ($item) => $item !== 'menunggu')) {
            return 'diproses';
        }

        return 'menunggu';
    }
}
