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
    /**
     * Potong stok saat pesanan dibuat (checkout keranjang).
     *
     * Stok dikunci lebih awal agar pesanan yang sudah masuk tidak
     * kehabisan stok saat pembayaran dilakukan.
     */
    public function potongSaatPesanan(Pesanan $pesanan, ?int $penggunaId = null): bool
    {
        return $this->potong($pesanan, $penggunaId, 'Stok dipotong saat pesanan dibuat ');
    }

    /**
     * Kompatibilitas untuk alur pembayaran lama. Jika stok sudah dipotong
     * saat pesanan dibuat, method ini tidak memotong dua kali.
     */
    public function potongSetelahPembayaran(Pesanan $pesanan, ?int $penggunaId = null): bool
    {
        return $this->potong($pesanan, $penggunaId, 'Stok dipotong setelah pembayaran ');
    }

    private function potong(Pesanan $pesanan, ?int $penggunaId, string $keterangan): bool
    {
        $pesanan->loadMissing('detailPesanan');

        if ($pesanan->detailPesanan->isEmpty()) {
            return false;
        }

        return DB::transaction(function () use ($pesanan, $penggunaId, $keterangan) {
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
                    throw new \RuntimeException(
                        'Stok '.$menu->nama_menu.' tersisa '.max(0, (int) $menu->stok_menu)
                        .' porsi, tidak cukup untuk '.$jumlah.' porsi.'
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
                    'keterangan' => $keterangan . $terkunci->kode_pesanan,
                    'created_by' => $penggunaId ?? auth()->id(),
                    'created_at' => now(),
                ]);
            }

            DB::afterCommit(fn () => Realtime::emit(new MenuDiperbarui('stok')));

            return true;
        });
    }
}
