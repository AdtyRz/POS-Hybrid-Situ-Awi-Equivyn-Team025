<?php

namespace App\Services;

use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\StokMutasi;
use Illuminate\Support\Facades\DB;

class PesananService
{
    public function buat(MejaMakan $meja, array $pilihanMenu, array $catatan = []): Pesanan
    {
        return DB::transaction(function () use ($meja, $pilihanMenu, $catatan) {
            $daftarMenu = Menu::whereIn('id', array_keys($pilihanMenu))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($pilihanMenu as $idMenu => $jumlah) {
                if ($jumlah <= 0) {
                    continue;
                }

                if (! isset($daftarMenu[$idMenu])) {
                    throw new \RuntimeException('Menu tidak ditemukan.');
                }

                if ($daftarMenu[$idMenu]->stok_menu < $jumlah) {
                    throw new \RuntimeException(
                        'Stok ' . $daftarMenu[$idMenu]->nama_menu . ' tidak cukup.'
                    );
                }
            }

            $pesanan = Pesanan::create([
                'kode_pesanan' => $this->kodePesanan(),
                'meja_id' => $meja->id,
                'pelanggan_id' => null,
                'kasir_id' => auth()->check() ? auth()->user()->id : null,
                'total_bayar' => 0,
                'status_pesanan' => Pesanan::STATUS_MENUNGGU,
                'status_pembayaran' => 'belum_bayar',
            ]);

            $total = 0;

            foreach ($pilihanMenu as $idMenu => $jumlah) {
                if ($jumlah <= 0) {
                    continue;
                }

                $menu = $daftarMenu[$idMenu];
                $subtotal = $menu->harga * $jumlah;

                $pesanan->detailPesanan()->create([
                    'menu_id' => $menu->id,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $menu->harga,
                    'subtotal' => $subtotal,
                    'catatan' => $catatan[$idMenu] ?? null,
                ]);

                $menu->decrement('stok_menu', $jumlah);

                StokMutasi::create([
                    'menu_id' => $menu->id,
                    'pesanan_id' => $pesanan->id,
                    'jenis' => 'penjualan',
                    'jumlah_delta' => -$jumlah,
                    'stok_akhir' => $menu->fresh()->stok_menu,
                ]);

                $total += $subtotal;
            }

            $pesanan->update(['total_bayar' => $total]);

            $meja->update(['status_meja' => 'terisi']);

            return $pesanan->fresh();
        });
    }

    public function kodePesanan(): string
    {
        do {
            $kode = 'AWI-' . now()->format('YmdHis') . '-' . random_int(100, 999);
        } while (Pesanan::where('kode_pesanan', $kode)->exists());

        return $kode;
    }
}