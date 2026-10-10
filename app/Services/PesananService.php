<?php

namespace App\Services;

use App\Models\DetailPesanan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;

class PesananService
{
    public function buat(MejaMakan $meja, array $jumlahPerMenu, array $catatanPerMenu = []): Pesanan
    {
        return DB::transaction(function () use ($meja, $jumlahPerMenu, $catatanPerMenu) {
            $daftarMenu = Menu::whereIn('id', array_keys($jumlahPerMenu))->get()->keyBy('id');

            $totalBayar = 0;
            foreach ($jumlahPerMenu as $menuId => $jumlah) {
                if ($jumlah <= 0) continue;
                $menu = $daftarMenu[$menuId] ?? null;
                if (! $menu) continue;
                $totalBayar += $menu->harga * $jumlah;
            }

            if ($totalBayar <= 0) {
                throw new \RuntimeException('Total bayar tidak valid.');
            }

            $pesanan = Pesanan::create([
                'kode_pesanan' => $this->generateKodePesanan(),
                'meja_id' => $meja->id,
                'total_bayar' => $totalBayar,
                'status_pesanan' => Pesanan::STATUS_MENUNGGU,
                'status_pembayaran' => 'belum_bayar',
            ]);

            foreach ($jumlahPerMenu as $menuId => $jumlah) {
                if ($jumlah <= 0) continue;
                $menu = $daftarMenu[$menuId] ?? null;
                if (! $menu) continue;

                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $menuId,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $menu->harga,
                    'subtotal' => $menu->harga * $jumlah,
                    'status_item' => 'menunggu',
                    'catatan' => $catatanPerMenu[$menuId] ?? null,
                ]);
            }

            $meja->update(['status_meja' => 'terisi']);

            return $pesanan->load(['detailPesanan', 'meja']);
        });
    }

    private function generateKodePesanan(): string
    {
        $prefix = 'AWI-';
        $date = now()->format('YmdHis');
        $random = str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);
        return $prefix . $date . '-' . $random;
    }
}
