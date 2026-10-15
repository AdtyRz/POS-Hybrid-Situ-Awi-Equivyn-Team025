<?php

namespace Database\Seeders;

use App\Models\MejaMakan;
use App\Models\SesiMeja;
use Illuminate\Database\Seeder;

class SesiMejaSeeder extends Seeder
{
    public function run(): void
    {
        MejaMakan::orderBy('kode_meja')->get()->each(function (MejaMakan $meja) {
            // aktifUntuk() hanya membuat sesi baru bila meja belum punya sesi aktif,
            // jadi seeder ini aman dijalankan berulang kali.
            SesiMeja::aktifUntuk($meja);
        });

        $this->command?->info('Sesi aktif dibuat untuk ' . MejaMakan::count() . ' meja.');
    }
}
