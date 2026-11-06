<?php

namespace App\Services;

use App\Events\MenuDiperbarui;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\StokMutasi;
use App\Support\Realtime;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StokService
{
    public function potongSetelahPembayaran(Pesanan $pesanan, ?int $penggunaId = null): bool
    {
        $pesanan->loadMissing('detailPesanan');

        if ($pesanan->detailPesanan->isEmpty()) {
            return false;
        }

        return DB::transaction(function () use ($pesanan, $penggunaId) {
            // Kunci pesanan lebih dulu agar dua pembayaran bersamaan tidak
            // sama-sama lolos dari pengecekan mutasi/stok.
            $terkunci = Pesanan::query()->whereKey($pesanan->id)->lockForUpdate()->first();

            if (! $terkunci) {
                return false;
            }

            $sudahDiproses = StokMutasi::query()
                ->where('pesanan_id', $terkunci->id)
                ->where('jenis', 'penjualan')
                ->exists();

            if ($sudahDiproses) {
                return false;
            }

            $jumlahPerMenu = $terkunci->detailPesanan()
                ->get()
                ->groupBy('menu_id')
                ->map(fn ($items) => (int) $items->sum('jumlah'));

            foreach ($jumlahPerMenu as $menuId => $jumlah) {
                if ($jumlah <= 0) {
                    continue;
                }

                $menu = Menu::query()->lockForUpdate()->find($menuId);

                if (! $menu) {
                    continue;
                }

                if ((int) $menu->stok_menu < $jumlah) {
                    throw new RuntimeException(
                        'Stok '.$menu->nama_menu.' tersisa '.max(0, (int) $menu->stok_menu)
                        .' porsi, tidak cukup untuk '.$jumlah.' porsi. Pembayaran dibatalkan.'
                    );
                }

                $stokAkhir = (int) $menu->stok_menu - $jumlah;

                $menu->update(['stok_menu' => $stokAkhir]);

                StokMutasi::create([
                    'menu_id' => $menu->id,
                    'pesanan_id' => $terkunci->id,
                    'jenis' => 'penjualan',
                    'jumlah_delta' => -$jumlah,
                    'stok_akhir' => $stokAkhir,
                    'keterangan' => 'Stok dipotong setelah pembayaran ' . $terkunci->kode_pesanan,
                    'created_by' => $penggunaId ?? auth()->id(),
                ]);
            }

            DB::afterCommit(fn () => Realtime::emit(new MenuDiperbarui('stok')));

            return true;
        });
    }
}
