<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SituAwiSeeder::class);
        $this->call(BahanBakuSeeder::class);
        $this->call(SesiMejaSeeder::class);

        // Riwayat pesanan memakai Faker, yang hanya tersedia di require-dev.
        // Di container produksi (composer install --no-dev) seeder ini dilewati
        // agar `php artisan db:seed` tetap berhasil.
        if (class_exists(\Faker\Generator::class)) {
            $this->call(RiwayatPesananSeeder::class);
        } else {
            $this->command?->warn('RiwayatPesananSeeder dilewati: Faker tidak tersedia di mode production.');
        }
    }
}
