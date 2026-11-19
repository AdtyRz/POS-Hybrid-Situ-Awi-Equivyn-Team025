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
        $this->call(RiwayatPesananSeeder::class);
    }
}
