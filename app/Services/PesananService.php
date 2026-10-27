<?php

namespace App\Services;

use App\Models\DetailPesanan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\StokMutasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PesananService
{
    /**
     * Buat pesanan dari satu pintu (e-menu, kasir, atau API).
     *
     * Stok sengaja tidak dipotong di sini. Sesuai alur POS, stok baru
     * dikurangi setelah pembayaran berhasil melalui StokService.
     *
     * Opsi yang didukung:
     * - asal: emenu|kasir|api (menentukan status awal)
     * - metode: tunai|qris
     * - kasir_id: int|null
     * - catatan: string|null (catatan tingkat pesanan)
     */
    public function buat(MejaMakan $meja, array $jumlahPerMenu, array $catatanPerMenu = [], array $opsi = []): Pesanan
    {
        return DB::transaction(function () use ($meja, $jumlahPerMenu, $catatanPerMenu, $opsi) {
            $dipilih = collect($jumlahPerMenu)
                ->filter(fn ($jumlah, $menuId) => is_numeric($menuId) && (int) $jumlah > 0)
                ->map(fn ($jumlah) => (int) $jumlah);

            if ($dipilih->isEmpty()) {
                throw new RuntimeException('Minimal 1 item harus dipesan.');
            }

            $daftarMenu = Menu::whereIn('id', $dipilih->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;

            foreach ($dipilih as $menuId => $jumlah) {
                $menu = $daftarMenu->get($menuId);

                if (! $menu) {
                    throw new RuntimeException('Menu yang dipilih tidak ditemukan.');
                }

                if (! $menu->is_aktif) {
                    throw new RuntimeException('Menu '.$menu->nama_menu.' sedang tidak tersedia.');
                }

                if ($menu->stok_menu < $jumlah) {
                    throw new RuntimeException('Stok '.$menu->nama_menu.' tidak cukup.');
                }

                $subtotal += (float) $menu->harga * $jumlah;
            }

            // Tidak ada pajak/biaya tambahan. Total tagihan murni subtotal menu.
            $totalBayar = $subtotal;

            $asal = $opsi['asal'] ?? 'api';
            $metode = $opsi['metode'] ?? null;

            if ($metode !== null && ! in_array($metode, ['tunai', 'qris'], true)) {
                throw new RuntimeException('Metode pembayaran tidak valid.');
            }

            if ($metode === null && $asal !== 'api') {
                $metode = 'tunai';
            }

            [$statusPesanan, $statusPembayaran] = $this->statusAwal($asal, $metode);

            $kodePesanan = $this->generateKodePesanan($meja);

            $pesanan = Pesanan::create([
                'kode_pesanan' => $kodePesanan,
                'kode_pembayaran' => $this->generateKodePembayaran(),
                'meja_id' => $meja->id,
                'kasir_id' => $opsi['kasir_id'] ?? null,
                'total_bayar' => $totalBayar,
                'metode_pembayaran' => $metode,
                'status_pesanan' => $statusPesanan,
                'status_pembayaran' => $statusPembayaran,
                'catatan' => $opsi['catatan'] ?? null,
            ]);

            foreach ($dipilih as $menuId => $jumlah) {
                $menu = $daftarMenu->get($menuId);

                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $menu->id,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $menu->harga,
                    'subtotal' => (float) $menu->harga * $jumlah,
                    'status_item' => 'menunggu',
                    'catatan' => $catatanPerMenu[$menuId] ?? null,
                ]);
            }

            $meja->update(['status_meja' => 'terisi']);

            return $pesanan->load(['detailPesanan', 'meja']);
        });
    }

    /**
     * Batalkan pesanan. Stok hanya dikembalikan bila stok memang sudah
     * dipotong setelah pembayaran. Proses idempoten agar void ganda aman.
     */
    public function batalkan(Pesanan $pesanan, ?int $userId = null, string $alasan = 'Pesanan dibatalkan.'): void
    {
        DB::transaction(function () use ($pesanan, $userId, $alasan) {
            $terkunci = Pesanan::whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

            if (in_array($terkunci->status_pesanan, [Pesanan::STATUS_SELESAI, Pesanan::STATUS_DIBATALKAN], true)) {
                return;
            }

            if ($terkunci->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
                throw new RuntimeException('Pesanan yang sudah lunas tidak dapat dibatalkan.');
            }

            $sudahDiproses = StokMutasi::query()
                ->where('pesanan_id', $terkunci->id)
                ->where('jenis', 'penjualan')
                ->exists();

            $sudahDikembalikan = StokMutasi::query()
                ->where('pesanan_id', $terkunci->id)
                ->where('jenis', 'pembatalan')
                ->exists();

            if ($sudahDiproses && ! $sudahDikembalikan) {
                $detail = $terkunci->detailPesanan()->get();

                foreach ($detail as $item) {
                    $menu = Menu::whereKey($item->menu_id)->lockForUpdate()->first();

                    if (! $menu) {
                        continue;
                    }

                    $menu->increment('stok_menu', $item->jumlah);

                    StokMutasi::create([
                        'menu_id' => $menu->id,
                        'pesanan_id' => $terkunci->id,
                        'jenis' => 'pembatalan',
                        'jumlah_delta' => $item->jumlah,
                        'stok_akhir' => (int) $menu->fresh()->stok_menu,
                        'keterangan' => $alasan,
                        'created_by' => $userId,
                    ]);
                }
            }

            $terkunci->update([
                'status_pesanan' => Pesanan::STATUS_DIBATALKAN,
                'void_by' => $userId,
                'void_at' => now(),
                'alasan_void' => $alasan,
            ]);
        });
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function statusAwal(string $asal, ?string $metode): array
    {
        if ($asal === 'emenu' && $metode === 'qris') {
            return [Pesanan::STATUS_MENUNGGU_VERIFIKASI, Pesanan::PAYMENT_PENDING_VERIFIKASI];
        }

        if ($asal === 'emenu') {
            return [Pesanan::STATUS_MENUNGGU_PEMBAYARAN, Pesanan::PAYMENT_PENDING_PEMBAYARAN];
        }

        return [Pesanan::STATUS_MENUNGGU, Pesanan::PAYMENT_BELUM_BAYAR];
    }

    private function generateKodePesanan(MejaMakan $meja): string
    {
        $dasar = 'SW-'.now()->format('YmdHis').'-'.$meja->id;
        $kode = $dasar;

        for ($i = 1; Pesanan::where('kode_pesanan', $kode)->exists(); $i++) {
            $kode = $dasar.'-'.$i;

            if ($i > 20) {
                $kode = $dasar.'-'.Str::upper(Str::random(3));
                break;
            }
        }

        return $kode;
    }

    private function generateKodePembayaran(): string
    {
        $dasar = 'PAY-'.now()->format('YmdHis').'-';

        do {
            $kode = $dasar.Str::upper(Str::random(6));
        } while (Pesanan::where('kode_pembayaran', $kode)->exists());

        return $kode;
    }
}
