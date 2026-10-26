<?php

namespace App\Services;

use App\Events\MenuDiperbarui;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\StokMutasi;
use App\Support\Realtime;
use Illuminate\Support\Facades\DB;

class StokService
{
    public function potongSetelahPembayaran(Pesanan $pesanan, ?int $penggunaId = null): bool
    {
        $pesanan->loadMissing('detailPesanan');

        if ($pesanan->detailPesanan->isEmpty()) {
            return false;
        }

        $sudahDiproses = StokMutasi::query()
            ->where('pesanan_id', $pesanan->id)
            ->where('jenis', 'penjualan')
            ->exists();

        if ($sudahDiproses) {
            return false;
        }

        $jumlahPerMenu = $pesanan->detailPesanan
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

            $menu->decrement('stok_menu', $jumlah);

            StokMutasi::create([
                'menu_id' => $menu->id,
                'pesanan_id' => $pesanan->id,
                'jenis' => 'penjualan',
                'jumlah_delta' => -$jumlah,
                'stok_akhir' => (int) $menu->fresh()->stok_menu,
                'keterangan' => 'Stok dipotong setelah pembayaran ' . $pesanan->kode_pesanan,
                'created_by' => $penggunaId ?? auth()->id(),
            ]);
        }

        DB::afterCommit(fn () => Realtime::emit(new MenuDiperbarui('stok')));

        return true;
    }
}
