<?php

namespace App\Console\Commands;

use App\Services\KeranjangService;
use Illuminate\Console\Command;

class BersihkanKeranjangKedaluwarsa extends Command
{
    protected $signature = 'keranjang:bersihkan';

    protected $description = 'Lepas reservasi stok pada keranjang pelanggan yang sudah kedaluwarsa';

    public function handle(KeranjangService $keranjangService): int
    {
        $jumlah = $keranjangService->bersihkanKedaluwarsa();

        $this->info($jumlah === 0
            ? 'Tidak ada keranjang kedaluwarsa.'
            : $jumlah.' keranjang kedaluwarsa dibersihkan.');

        return self::SUCCESS;
    }
}
