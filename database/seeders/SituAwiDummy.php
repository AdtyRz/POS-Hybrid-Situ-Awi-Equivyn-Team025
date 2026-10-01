<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SituAwiDummy extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin Utama',
                'email' => 'admin@situawi.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kasir Saung',
                'email' => 'kasir@situawi.com',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pelayan Saung',
                'email' => 'pelayan@situawi.com',
                'password' => Hash::make('password123'),
                'role' => 'pelayan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Koki Dapur',
                'email' => 'koki@situawi.com',
                'password' => Hash::make('password123'),
                'role' => 'koki',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Barista Bar',
                'email' => 'barista@situawi.com',
                'password' => Hash::make('password123'),
                'role' => 'barista',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $mejas = [
            ['nomor_meja' => 'LB-01', 'lokasi_area' => 'Lesehan Bawah'],
            ['nomor_meja' => 'LB-02', 'lokasi_area' => 'Lesehan Bawah'],
            ['nomor_meja' => 'LB-03', 'lokasi_area' => 'Lesehan Bawah'],
            ['nomor_meja' => 'LB-04', 'lokasi_area' => 'Lesehan Bawah'],
            ['nomor_meja' => 'LB-05', 'lokasi_area' => 'Lesehan Bawah'],
            ['nomor_meja' => 'LA-01', 'lokasi_area' => 'Lesehan Atas'],
            ['nomor_meja' => 'LA-02', 'lokasi_area' => 'Lesehan Atas'],
            ['nomor_meja' => 'KA-01', 'lokasi_area' => 'Kursi Atas'],
            ['nomor_meja' => 'KA-02', 'lokasi_area' => 'Kursi Atas'],
            ['nomor_meja' => 'KA-03', 'lokasi_area' => 'Kursi Atas'],
            ['nomor_meja' => 'KA-04', 'lokasi_area' => 'Kursi Atas'],
        ];

        foreach ($mejas as $meja) {
            DB::table('meja_makan')->insert([
                'nomor_meja' => $meja['nomor_meja'],
                'kode_qr_token' => 'QR-' . $meja['nomor_meja'] . '-' . Str::random(6),
                'lokasi_area' => $meja['lokasi_area'],
                'status_aktif' => true,
                'status_meja' => 'kosong',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $kategoriMakananId = DB::table('kategori')->insertGetId([
            'nama_kategori' => 'Makanan',
            'target_kds' => 'dapur',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kategoriMinumanId = DB::table('kategori')->insertGetId([
            'nama_kategori' => 'Minuman',
            'target_kds' => 'bar',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $makanan = [
            ['nama_menu' => 'Paket Nasi Liwet 5 Orang', 'harga' => 150000],
            ['nama_menu' => 'Paket Nasi Liwet 5 Orang Plus Karedok', 'harga' => 175000],
            ['nama_menu' => 'Nasi Rempah Ayam Sambel Rica', 'harga' => 25000],
            ['nama_menu' => 'Nasi Rempah Ayam Sambel Ijo', 'harga' => 25000],
            ['nama_menu' => 'Nasi Rempah Ayam Serundeng', 'harga' => 25000],
            ['nama_menu' => 'Nasi Tutug Oncom Ayam', 'harga' => 25000],
            ['nama_menu' => 'Nasi Telor Bakar', 'harga' => 12000],
            ['nama_menu' => 'Baso Aci Ceker + Mie', 'harga' => 12000],
            ['nama_menu' => 'Baso Aci Ceker', 'harga' => 10000],
            ['nama_menu' => 'Ayam Bakar / Goreng', 'harga' => 12000],
            ['nama_menu' => 'Gepuk', 'harga' => 15000],
            ['nama_menu' => 'Pepes Ikan', 'harga' => 15000],
            ['nama_menu' => 'Pepes Ayam', 'harga' => 15000],
            ['nama_menu' => 'Pepes Tahu', 'harga' => 5000],
            ['nama_menu' => 'Jengkol', 'harga' => 15000],
            ['nama_menu' => 'Sayur Asem', 'harga' => 10000],
            ['nama_menu' => 'Kangkung', 'harga' => 10000],
            ['nama_menu' => 'Nasi Rempah (Ala Carte)', 'harga' => 12000],
            ['nama_menu' => 'Nasi Tutug Oncom (Ala Carte)', 'harga' => 10000],
            ['nama_menu' => 'Nasi Putih', 'harga' => 5000],
            ['nama_menu' => 'Wing Ceker Ngajerit', 'harga' => 12000],
            ['nama_menu' => 'Spaghetty Original', 'harga' => 12000],
            ['nama_menu' => 'Spaghetty Seuhah', 'harga' => 12000],
            ['nama_menu' => 'Tahu Seuhah', 'harga' => 10000],
            ['nama_menu' => 'Tahu Pletok', 'harga' => 10000],
            ['nama_menu' => 'Roti Bakar Kasino', 'harga' => 15000],
            ['nama_menu' => 'Roti Bakar Kadet', 'harga' => 8000],
            ['nama_menu' => 'Pisang Keju', 'harga' => 10000],
            ['nama_menu' => 'Pisang Keju Coklat', 'harga' => 10000],
            ['nama_menu' => 'Cheese Roll', 'harga' => 10000],
            ['nama_menu' => 'Pisang Roll', 'harga' => 10000],
        ];

        foreach ($makanan as $item) {
            DB::table('menu')->insert([
                'kategori_id' => $kategoriMakananId,
                'nama_menu' => $item['nama_menu'],
                'foto_menu' => null,
                'harga' => $item['harga'],
                'stok_menu' => 50,
                'status_tersedia' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $minuman = [
            ['nama_menu' => 'Vietnam Drip Arabica Coffee', 'harga' => 10000],
            ['nama_menu' => 'Vietnam Drip Robusta Coffee', 'harga' => 8000],
            ['nama_menu' => 'Vietnam Drip Blend Coffee', 'harga' => 10000],
            ['nama_menu' => 'Moka Pot Arabica Coffee', 'harga' => 10000],
            ['nama_menu' => 'Moka Pot Robusta Coffee', 'harga' => 8000],
            ['nama_menu' => 'Moka Pot Blend Coffee', 'harga' => 10000],
            ['nama_menu' => 'V60 Arabica Coffee', 'harga' => 10000],
            ['nama_menu' => 'V60 Robusta Coffee', 'harga' => 10000],
            ['nama_menu' => 'V60 Blend Coffee', 'harga' => 8000],
            ['nama_menu' => 'Tubruk Arabica Coffee', 'harga' => 10000],
            ['nama_menu' => 'Tubruk Robusta Coffee', 'harga' => 10000],
            ['nama_menu' => 'Tubruk Blend Coffee', 'harga' => 10000],
            ['nama_menu' => 'Arabika Ice Coffee', 'harga' => 10000],
        ];

        foreach ($minuman as $item) {
            DB::table('menu')->insert([
                'kategori_id' => $kategoriMinumanId,
                'nama_menu' => $item['nama_menu'],
                'foto_menu' => null,
                'harga' => $item['harga'],
                'stok_menu' => 50,
                'status_tersedia' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
