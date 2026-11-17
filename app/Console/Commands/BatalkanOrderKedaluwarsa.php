<?php

namespace App\Console\Commands;

use App\Events\StatusPesananBerubah;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Services\PesananService;
use App\Support\Realtime;
use Illuminate\Console\Command;

class BatalkanOrderKedaluwarsa extends Command
{
    protected $signature = 'pesanan:batalkan-kedaluwarsa';

    protected $description = 'Batalkan pesanan QRIS yang melewati batas waktu pembayaran bila opsi otomatis aktif';

    public function handle(PesananService $pesananService): int
    {
        if (! filter_var(Pengaturan::nilai('midtrans_auto_cancel', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $this->info('Pembatalan otomatis nonaktif. Tidak ada pesanan yang diproses.');

            return self::SUCCESS;
        }

        $dibatalkan = 0;
        $dilewati = 0;

        Pesanan::query()
            ->with('meja')
            ->where('metode_pembayaran', 'qris')
            ->whereNotIn('status_pesanan', [Pesanan::STATUS_SELESAI, Pesanan::STATUS_DIBATALKAN])
            ->where('status_pembayaran', '!=', Pesanan::PAYMENT_SUDAH_BAYAR)
            ->whereNotNull('qris_expires_at')
            ->where('qris_expires_at', '<=', now())
            ->chunkById(100, function ($daftar) use ($pesananService, &$dibatalkan, &$dilewati) {
                foreach ($daftar as $pesanan) {
                    try {
                        $pesananService->batalkan(
                            $pesanan,
                            null,
                            'Dibatalkan otomatis: batas waktu pembayaran QRIS habis.',
                        );
                    } catch (\RuntimeException $exception) {
                        $dilewati++;
                        $this->warn("Dilewati {$pesanan->kode_pesanan}: {$exception->getMessage()}");

                        continue;
                    }

                    $pesanan->refresh();
                    $dibatalkan++;

                    Realtime::emit(new StatusPesananBerubah([
                        'kode_pesanan' => $pesanan->kode_pesanan,
                        'meja' => $pesanan->meja->kode_meja ?? null,
                        'status_pesanan' => $pesanan->status_pesanan,
                        'status_pembayaran' => $pesanan->status_pembayaran,
                        'metode_pembayaran' => $pesanan->metode_pembayaran,
                    ]));

                    $this->line("Dibatalkan {$pesanan->kode_pesanan} (QRIS kedaluwarsa).");
                }
            });

        $this->info("Selesai. Dibatalkan: {$dibatalkan}, dilewati: {$dilewati}.");

        return self::SUCCESS;
    }
}
