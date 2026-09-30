<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * SEEDER DATA AWAL SITU AWI  (rev v1.1 - 20260929)
 * =================================================
 * Isi: 6 akun peran + 11 meja saung + 2 kategori + 44 menu (31 makanan, 13 minuman)
 *
 * PERBAIKAN v1.1 (seeder lama tidak akan jalan / crash terhadap skema kanonik):
 *  1. users.name           -> users.nama          (bukan 'name')
 *  2. meja_makan.nomor_meja-> meja_makan.kode_meja
 *  3. meja_makan.lokasi_area-> meja_makan.area
 *  4. meja_makan.kode_qr_token -> meja_makan.qr_token
 *  5. meal 'status_aktif'  -> DIHAPUS (kolom tidak ada di skema kanonik)
 *  6. menu 'status_tersedia' -> DIHAPUS (diturunkan dari stok_menu > 0)
 *  7. menu wajib punya 'slug' UNIQUE  -> DITAMBAHKAN (dulu tidak ada sama sekali)
 *  8. role 'pelanggan'     -> DITAMBAHKAN (FR-003 tamu QR tidak punya akun)
 *  9. kolom 'kapasitas'    -> DITAMBAHKAN (meja saung 4-6 orang)
 *
 * Catatan keamanan: password di bawah HANYA berlaku untuk demo lokal UKK.
 * Password TIDAK PERNAH ditulis sebagai hash manual; selalu lewat Hash::make().
 */
class SituAwiSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ---------------------------------------------------- 1. USERS (6 role)
        DB::table('users')->insert([
            [
                'nama' => 'Admin Utama', 'email' => 'admin@situawi.com',
                'password' => Hash::make('password123'), 'role' => 'admin',
                'no_telp' => '081200000001', 'is_aktif' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'nama' => 'Kasir Saung', 'email' => 'kasir@situawi.com',
                'password' => Hash::make('password123'), 'role' => 'kasir',
                'no_telp' => '081200000002', 'is_aktif' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'nama' => 'Pelayan Saung', 'email' => 'pelayan@situawi.com',
                'password' => Hash::make('password123'), 'role' => 'pelayan',
                'no_telp' => '081200000003', 'is_aktif' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'nama' => 'Koki Dapur', 'email' => 'koki@situawi.com',
                'password' => Hash::make('password123'), 'role' => 'koki',
                'no_telp' => '081200000004', 'is_aktif' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'nama' => 'Barista Bar', 'email' => 'barista@situawi.com',
                'password' => Hash::make('password123'), 'role' => 'barista',
                'no_telp' => '081200000005', 'is_aktif' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                // FR-003 : tamu memindai QR Meja, akun dibuat otomatis oleh sistem.
                'nama' => 'Pelanggan Demo', 'email' => 'pelanggan@situawi.com',
                'password' => Hash::make('password123'), 'role' => 'pelanggan',
                'no_telp' => '081200000006', 'is_aktif' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);

        // ------------------------------------------- 2. MEJA MAKAN (11 saung)
        $mejas = [
            ['kode_meja' => 'LB-01', 'area' => 'Lesehan Bawah', 'kapasitas' => 6],
            ['kode_meja' => 'LB-02', 'area' => 'Lesehan Bawah', 'kapasitas' => 6],
            ['kode_meja' => 'LB-03', 'area' => 'Lesehan Bawah', 'kapasitas' => 6],
            ['kode_meja' => 'LB-04', 'area' => 'Lesehan Bawah', 'kapasitas' => 6],
            ['kode_meja' => 'LB-05', 'area' => 'Lesehan Bawah', 'kapasitas' => 6],
            ['kode_meja' => 'LA-01', 'area' => 'Lesehan Atas',  'kapasitas' => 4],
            ['kode_meja' => 'LA-02', 'area' => 'Lesehan Atas',  'kapasitas' => 4],
            ['kode_meja' => 'KA-01', 'area' => 'Kursi Atas',    'kapasitas' => 4],
            ['kode_meja' => 'KA-02', 'area' => 'Kursi Atas',    'kapasitas' => 4],
            ['kode_meja' => 'KA-03', 'area' => 'Kursi Atas',    'kapasitas' => 4],
            ['kode_meja' => 'KA-04', 'area' => 'Kursi Atas',    'kapasitas' => 4],
        ];

        foreach ($mejas as $meja) {
            DB::table('meja_makan')->insert([
                'kode_meja'  => $meja['kode_meja'],
                'area'       => $meja['area'],
                'kapasitas'  => $meja['kapasitas'],
                'qr_token'   => 'QR-' . str_replace('-', '', $meja['kode_meja']) . Str::upper(Str::random(6)),
                'id_device'  => null, // diisi saat pairing ESP32 Table Node (FR-006)
                'status_meja'=> 'kosong',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ------------------------------------------------ 3. KATEGORI (2 KDS)
        $idDapur = DB::table('kategori')->insertGetId([
            'nama_kategori' => 'Makanan', 'target_kds' => 'dapur',
            'deskripsi' => 'Menu yang dimasak di dapur - dikirim ke KDS Dapur',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $idBar = DB::table('kategori')->insertGetId([
            'nama_kategori' => 'Minuman', 'target_kds' => 'bar',
            'deskripsi' => 'Menu yang diracik di bar - dikirim ke KDS Bar',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // ------------------------------- 4. MENU : 31 MAKANAN (KDS Dapur)
        $makanan = [
            ['Paket Nasi Liwet 5 Orang', 150000],
            ['Paket Nasi Liwet 5 Orang Plus Karedok', 175000],
            ['Nasi Rempah Ayam Sambel Rica', 25000],
            ['Nasi Rempah Ayam Sambel Ijo', 25000],
            ['Nasi Rempah Ayam Serundeng', 25000],
            ['Nasi Tutug Oncom Ayam', 25000],
            ['Nasi Telor Bakar', 12000],
            ['Baso Aci Ceker Plus Mie', 12000],
            ['Baso Aci Ceker', 10000],
            ['Ayam Bakar Goreng', 12000],
            ['Gepuk', 15000],
            ['Pepes Ikan', 15000],
            ['Pepes Ayam', 15000],
            ['Pepes Tahu', 5000],
            ['Jengkol', 15000],
            ['Sayur Asem', 10000],
            ['Kangkung', 10000],
            ['Nasi Rempah Ala Carte', 12000],
            ['Nasi Tutug Oncom Ala Carte', 10000],
            ['Nasi Putih', 5000],
            ['Wing Ceker Ngajerit', 12000],
            ['Spaghetty Original', 12000],
            ['Spaghetty Seuhah', 12000],
            ['Tahu Seuhah', 10000],
            ['Tahu Pletok', 10000],
            ['Roti Bakar Kasino', 15000],
            ['Roti Bakar Kadet', 8000],
            ['Pisang Keju', 10000],
            ['Pisang Keju Coklat', 10000],
            ['Cheese Roll', 10000],
            ['Pisang Roll', 10000],
        ];

        $this->insertMenu($idDapur, $makanan, $now);

        // --------------------------------- 5. MENU : 13 MINUMAN (KDS Bar)
        $minuman = [
            ['Vietnam Drip Arabica Coffee', 10000],
            ['Vietnam Drip Robusta Coffee', 8000],
            ['Vietnam Drip Blend Coffee', 10000],
            ['Moka Pot Arabica Coffee', 10000],
            ['Moka Pot Robusta Coffee', 8000],
            ['Moka Pot Blend Coffee', 10000],
            ['V60 Arabica Coffee', 10000],
            ['V60 Robusta Coffee', 10000],
            ['V60 Blend Coffee', 8000],
            ['Tubruk Arabica Coffee', 10000],
            ['Tubruk Robusta Coffee', 10000],
            ['Tubruk Blend Coffee', 10000],
            ['Arabika Ice Coffee', 10000],
        ];

        $this->insertMenu($idBar, $minuman, $now);
    }

    /**
     * Insert menu dengan slug unik otomatis.
     * stok_menu diisi 50 sebagai stok pembuka untuk seluruh item.
     */
    private function insertMenu(int $kategoriId, array $items, $now): void
    {
        foreach ($items as [$nama, $harga]) {
            DB::table('menu')->insert([
                'kategori_id' => $kategoriId,
                'nama_menu'   => $nama,
                'slug'        => Str::slug($nama),
                'deskripsi'   => null,
                'foto_menu'   => null,
                'harga'       => $harga,
                'stok_menu'   => 50,
                'created_at'  => $now, 'updated_at' => $now,
            ]);
        }
    }
}
