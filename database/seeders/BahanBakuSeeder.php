<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\PembelianBahan;
use App\Models\Pengeluaran;
use App\Models\ResepMenu;
use App\Services\BahanStokService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BahanBakuSeeder extends Seeder
{
    /**
     * Master bahan baku demo.
     *
     * Format: nama => [satuan, stok minimum, harga satuan, jumlah pembelian awal]
     */
    private const BAHAN = [
        'Beras' => ['kg', 20, 14000, 40],
        'Ayam' => ['kg', 10, 38000, 15],
        'Ikan Nila' => ['kg', 5, 35000, 6],
        'Daging Sapi' => ['kg', 3, 120000, 4],
        'Telur' => ['butir', 30, 2500, 72],
        'Tahu' => ['papan', 10, 5000, 16],
        'Tempe' => ['papan', 5, 6000, 8],
        'Oncom' => ['papan', 5, 4000, 6],
        'Ceker Ayam' => ['kg', 5, 15000, 6],
        'Bakso Aci' => ['kg', 5, 10000, 6],
        'Jengkol' => ['kg', 3, 25000, 4],
        'Mie Kuning' => ['kg', 3, 15000, 4],
        'Sayur Asem' => ['ikat', 5, 5000, 8],
        'Kangkung' => ['ikat', 5, 4000, 10],
        'Kacang Panjang' => ['kg', 3, 12000, 4],
        'Kol' => ['kg', 3, 10000, 4],
        'Terong' => ['kg', 3, 10000, 4],
        'Labu Siam' => ['kg', 3, 8000, 4],
        'Timun' => ['kg', 3, 8000, 4],
        'Bawang Merah' => ['kg', 3, 35000, 4],
        'Bawang Putih' => ['kg', 2, 40000, 3],
        'Bawang Daun' => ['ikat', 3, 3000, 4],
        'Cabai Merah' => ['kg', 2, 45000, 3],
        'Cabai Rawit' => ['kg', 2, 50000, 2.5],
        'Cabai Hijau' => ['kg', 1, 40000, 2],
        'Kemiri' => ['kg', 1, 60000, 1.5],
        'Jahe' => ['kg', 1, 30000, 2],
        'Kencur' => ['kg', 1, 30000, 1.5],
        'Kunyit' => ['kg', 1, 25000, 1.5],
        'Lengkuas' => ['kg', 1, 20000, 1.5],
        'Serai' => ['batang', 20, 1000, 50],
        'Daun Salam' => ['lembar', 50, 100, 200],
        'Daun Jeruk' => ['lembar', 50, 100, 200],
        'Daun Kemangi' => ['ikat', 3, 3000, 4],
        'Daun Pisang' => ['lembar', 20, 500, 50],
        'Santan' => ['liter', 3, 25000, 5],
        'Minyak Goreng' => ['liter', 5, 18000, 10],
        'Gula Pasir' => ['kg', 3, 16000, 6],
        'Gula Merah' => ['kg', 2, 18000, 3],
        'Gula Aren' => ['kg', 1, 25000, 2],
        'Garam' => ['kg', 2, 5000, 4],
        'Kecap Manis' => ['liter', 2, 30000, 3],
        'Kelapa Parut' => ['kg', 2, 15000, 3],
        'Kacang Tanah' => ['kg', 1, 25000, 2],
        'Tepung Terigu' => ['kg', 3, 12000, 6],
        'Tepung Bumbu' => ['kg', 2, 15000, 3],
        'Pasta Spaghetti' => ['kg', 2, 25000, 3],
        'Saus Tomat' => ['liter', 2, 25000, 3],
        'Saus Sambal' => ['liter', 2, 30000, 3],
        'Roti Tawar' => ['bungkus', 5, 15000, 6],
        'Selai Coklat' => ['kg', 1, 50000, 2],
        'Coklat Bubuk' => ['kg', 1, 90000, 1.5],
        'Keju Cheddar' => ['kg', 1, 120000, 1.5],
        'Susu Kental Manis' => ['kaleng', 6, 12000, 12],
        'Susu UHT' => ['liter', 3, 20000, 5],
        'Es Batu' => ['kg', 5, 3000, 10],
        'Pisang' => ['sisir', 5, 12000, 8],
        'Kerupuk' => ['bungkus', 5, 5000, 6],
        'Kopi Arabika' => ['kg', 1, 180000, 3],
        'Kopi Robusta' => ['kg', 1, 120000, 3],
        'Kopi Blend' => ['kg', 1, 150000, 3],
    ];

    /**
     * Resep untuk seluruh menu yang dibuat SituAwiSeeder.
     *
     * Format: nama menu => [nama bahan => jumlah per porsi]
     */
    private const RESEP = [
        'Paket Nasi Liwet 5 Orang' => [
            'Beras' => 1, 'Ayam' => 1, 'Santan' => 0.5, 'Bawang Merah' => 0.15,
            'Bawang Putih' => 0.1, 'Cabai Merah' => 0.1, 'Cabai Rawit' => 0.05,
            'Serai' => 3, 'Daun Salam' => 5, 'Daun Jeruk' => 5, 'Minyak Goreng' => 0.1,
            'Telur' => 3, 'Timun' => 0.2, 'Daun Kemangi' => 0.1, 'Garam' => 0.02,
        ],
        'Paket Nasi Liwet 5 Orang Plus Karedok' => [
            'Beras' => 1, 'Ayam' => 1, 'Santan' => 0.5, 'Bawang Merah' => 0.15,
            'Bawang Putih' => 0.1, 'Cabai Merah' => 0.1, 'Cabai Rawit' => 0.08,
            'Serai' => 3, 'Daun Salam' => 5, 'Daun Jeruk' => 5, 'Minyak Goreng' => 0.1,
            'Telur' => 3, 'Timun' => 0.4, 'Daun Kemangi' => 0.1, 'Garam' => 0.02,
            'Kacang Panjang' => 0.3, 'Kol' => 0.2, 'Kacang Tanah' => 0.1, 'Gula Merah' => 0.05,
        ],
        'Nasi Rempah Ayam Sambel Rica' => [
            'Beras' => 0.15, 'Ayam' => 0.15, 'Cabai Merah' => 0.03, 'Cabai Rawit' => 0.02,
            'Bawang Merah' => 0.02, 'Bawang Putih' => 0.01, 'Minyak Goreng' => 0.02, 'Garam' => 0.005,
        ],
        'Nasi Rempah Ayam Sambel Ijo' => [
            'Beras' => 0.15, 'Ayam' => 0.15, 'Cabai Hijau' => 0.04, 'Cabai Rawit' => 0.015,
            'Bawang Merah' => 0.02, 'Bawang Putih' => 0.01, 'Minyak Goreng' => 0.02, 'Garam' => 0.005,
        ],
        'Nasi Rempah Ayam Serundeng' => [
            'Beras' => 0.15, 'Ayam' => 0.15, 'Kelapa Parut' => 0.05, 'Bawang Merah' => 0.02,
            'Bawang Putih' => 0.01, 'Cabai Merah' => 0.01, 'Gula Merah' => 0.005,
            'Minyak Goreng' => 0.02, 'Garam' => 0.005,
        ],
        'Nasi Tutug Oncom Ayam' => [
            'Beras' => 0.15, 'Oncom' => 0.05, 'Ayam' => 0.15, 'Bawang Merah' => 0.02,
            'Bawang Putih' => 0.01, 'Cabai Rawit' => 0.01, 'Minyak Goreng' => 0.02, 'Garam' => 0.005,
        ],
        'Nasi Telor Bakar' => [
            'Beras' => 0.15, 'Telur' => 1, 'Kecap Manis' => 0.015, 'Bawang Merah' => 0.01,
            'Cabai Rawit' => 0.01, 'Minyak Goreng' => 0.01, 'Garam' => 0.005,
        ],
        'Baso Aci Ceker Plus Mie' => [
            'Bakso Aci' => 0.1, 'Ceker Ayam' => 0.1, 'Mie Kuning' => 0.08,
            'Cabai Rawit' => 0.02, 'Bawang Merah' => 0.01, 'Bawang Putih' => 0.005,
            'Minyak Goreng' => 0.01, 'Garam' => 0.003,
        ],
        'Baso Aci Ceker' => [
            'Bakso Aci' => 0.1, 'Ceker Ayam' => 0.12, 'Cabai Rawit' => 0.02,
            'Bawang Merah' => 0.01, 'Bawang Putih' => 0.005, 'Minyak Goreng' => 0.01, 'Garam' => 0.003,
        ],
        'Ayam Bakar Goreng' => [
            'Ayam' => 0.2, 'Kecap Manis' => 0.02, 'Bawang Merah' => 0.02,
            'Bawang Putih' => 0.01, 'Cabai Rawit' => 0.01, 'Gula Merah' => 0.005,
            'Minyak Goreng' => 0.02, 'Garam' => 0.003,
        ],
        'Gepuk' => [
            'Daging Sapi' => 0.08, 'Santan' => 0.05, 'Gula Merah' => 0.02,
            'Bawang Merah' => 0.015, 'Bawang Putih' => 0.01, 'Lengkuas' => 0.005,
            'Daun Salam' => 1, 'Garam' => 0.003,
        ],
        'Pepes Ikan' => [
            'Ikan Nila' => 0.18, 'Daun Kemangi' => 0.02, 'Cabai Rawit' => 0.02,
            'Bawang Merah' => 0.02, 'Bawang Putih' => 0.01, 'Daun Pisang' => 1, 'Garam' => 0.003,
        ],
        'Pepes Ayam' => [
            'Ayam' => 0.2, 'Daun Kemangi' => 0.02, 'Cabai Rawit' => 0.02,
            'Bawang Merah' => 0.02, 'Bawang Putih' => 0.01, 'Daun Pisang' => 1, 'Garam' => 0.003,
        ],
        'Pepes Tahu' => [
            'Tahu' => 0.25, 'Daun Kemangi' => 0.02, 'Cabai Rawit' => 0.015,
            'Bawang Merah' => 0.01, 'Bawang Putih' => 0.005, 'Daun Pisang' => 1, 'Garam' => 0.002,
        ],
        'Jengkol' => [
            'Jengkol' => 0.15, 'Cabai Rawit' => 0.02, 'Bawang Merah' => 0.015,
            'Bawang Putih' => 0.01, 'Gula Merah' => 0.005, 'Minyak Goreng' => 0.02, 'Garam' => 0.002,
        ],
        'Sayur Asem' => [
            'Sayur Asem' => 1, 'Kacang Panjang' => 0.05, 'Labu Siam' => 0.05,
            'Gula Merah' => 0.01, 'Bawang Merah' => 0.01, 'Bawang Putih' => 0.005,
            'Daun Salam' => 1, 'Garam' => 0.003,
        ],
        'Kangkung' => [
            'Kangkung' => 1, 'Bawang Merah' => 0.01, 'Bawang Putih' => 0.005,
            'Cabai Rawit' => 0.01, 'Minyak Goreng' => 0.01, 'Garam' => 0.002,
        ],
        'Nasi Rempah Ala Carte' => [
            'Beras' => 0.15, 'Santan' => 0.02, 'Serai' => 1, 'Daun Salam' => 2,
            'Bawang Merah' => 0.01, 'Bawang Putih' => 0.005, 'Garam' => 0.002,
        ],
        'Nasi Tutug Oncom Ala Carte' => [
            'Beras' => 0.15, 'Oncom' => 0.05, 'Bawang Merah' => 0.01,
            'Bawang Putih' => 0.005, 'Cabai Rawit' => 0.01, 'Garam' => 0.002,
        ],
        'Nasi Putih' => [
            'Beras' => 0.12, 'Garam' => 0.001,
        ],
        'Wing Ceker Ngajerit' => [
            'Ceker Ayam' => 0.15, 'Cabai Rawit' => 0.03, 'Bawang Merah' => 0.015,
            'Bawang Putih' => 0.01, 'Gula Merah' => 0.005, 'Minyak Goreng' => 0.02, 'Garam' => 0.003,
        ],
        'Spaghetty Original' => [
            'Pasta Spaghetti' => 0.1, 'Saus Tomat' => 0.08, 'Daging Sapi' => 0.03,
            'Bawang Putih' => 0.005, 'Keju Cheddar' => 0.005, 'Minyak Goreng' => 0.01, 'Garam' => 0.002,
        ],
        'Spaghetty Seuhah' => [
            'Pasta Spaghetti' => 0.1, 'Saus Sambal' => 0.08, 'Cabai Rawit' => 0.02,
            'Bawang Putih' => 0.005, 'Minyak Goreng' => 0.01, 'Garam' => 0.002,
        ],
        'Tahu Seuhah' => [
            'Tahu' => 0.3, 'Tepung Bumbu' => 0.03, 'Cabai Rawit' => 0.02,
            'Minyak Goreng' => 0.03, 'Garam' => 0.002,
        ],
        'Tahu Pletok' => [
            'Tahu' => 0.3, 'Tepung Bumbu' => 0.03, 'Kecap Manis' => 0.01,
            'Minyak Goreng' => 0.03, 'Garam' => 0.002,
        ],
        'Roti Bakar Kasino' => [
            'Roti Tawar' => 0.2, 'Keju Cheddar' => 0.02, 'Susu Kental Manis' => 0.03, 'Gula Pasir' => 0.01,
        ],
        'Roti Bakar Kadet' => [
            'Roti Tawar' => 0.2, 'Selai Coklat' => 0.03, 'Gula Pasir' => 0.005,
        ],
        'Pisang Keju' => [
            'Pisang' => 0.25, 'Keju Cheddar' => 0.02, 'Tepung Terigu' => 0.03,
            'Minyak Goreng' => 0.02, 'Gula Pasir' => 0.01,
        ],
        'Pisang Keju Coklat' => [
            'Pisang' => 0.25, 'Keju Cheddar' => 0.02, 'Selai Coklat' => 0.02,
            'Tepung Terigu' => 0.03, 'Minyak Goreng' => 0.02, 'Gula Pasir' => 0.01,
        ],
        'Cheese Roll' => [
            'Tepung Terigu' => 0.05, 'Keju Cheddar' => 0.03, 'Susu UHT' => 0.02,
            'Gula Pasir' => 0.01, 'Minyak Goreng' => 0.02,
        ],
        'Pisang Roll' => [
            'Tepung Terigu' => 0.05, 'Pisang' => 0.2, 'Gula Pasir' => 0.01, 'Minyak Goreng' => 0.02,
        ],
        'Vietnam Drip Arabica Coffee' => [
            'Kopi Arabika' => 0.018, 'Susu Kental Manis' => 0.02,
        ],
        'Vietnam Drip Robusta Coffee' => [
            'Kopi Robusta' => 0.018, 'Susu Kental Manis' => 0.02,
        ],
        'Vietnam Drip Blend Coffee' => [
            'Kopi Blend' => 0.018, 'Susu Kental Manis' => 0.02,
        ],
        'Moka Pot Arabica Coffee' => [
            'Kopi Arabika' => 0.015, 'Gula Pasir' => 0.005,
        ],
        'Moka Pot Robusta Coffee' => [
            'Kopi Robusta' => 0.015, 'Gula Pasir' => 0.005,
        ],
        'Moka Pot Blend Coffee' => [
            'Kopi Blend' => 0.015, 'Gula Pasir' => 0.005,
        ],
        'V60 Arabica Coffee' => [
            'Kopi Arabika' => 0.015, 'Gula Pasir' => 0.005,
        ],
        'V60 Robusta Coffee' => [
            'Kopi Robusta' => 0.015, 'Gula Pasir' => 0.005,
        ],
        'V60 Blend Coffee' => [
            'Kopi Blend' => 0.015, 'Gula Pasir' => 0.005,
        ],
        'Tubruk Arabica Coffee' => [
            'Kopi Arabika' => 0.012, 'Gula Pasir' => 0.01,
        ],
        'Tubruk Robusta Coffee' => [
            'Kopi Robusta' => 0.012, 'Gula Pasir' => 0.01,
        ],
        'Tubruk Blend Coffee' => [
            'Kopi Blend' => 0.012, 'Gula Pasir' => 0.01,
        ],
        'Arabika Ice Coffee' => [
            'Kopi Arabika' => 0.015, 'Gula Aren' => 0.02, 'Susu UHT' => 0.1, 'Es Batu' => 0.1,
        ],
    ];

    /**
     * Contoh pembelian awal per pemasok.
     * Offset = jumlah hari dari awal bulan berjalan.
     */
    private const PEMBELIAN = [
        [
            'supplier' => 'Pasar Induk Caringin',
            'keterangan' => 'Belanja protein dan sayur segar',
            'offset' => 1,
            'bahan' => [
                'Ayam', 'Ikan Nila', 'Daging Sapi', 'Telur', 'Tahu', 'Tempe', 'Oncom',
                'Ceker Ayam', 'Bakso Aci', 'Jengkol', 'Sayur Asem', 'Kangkung',
                'Kacang Panjang', 'Kol', 'Terong', 'Labu Siam', 'Timun', 'Bawang Merah',
                'Bawang Putih', 'Bawang Daun', 'Cabai Merah', 'Cabai Rawit', 'Cabai Hijau',
                'Kemiri', 'Jahe', 'Kencur', 'Kunyit', 'Lengkuas', 'Serai', 'Daun Salam',
                'Daun Jeruk', 'Daun Kemangi', 'Daun Pisang', 'Kelapa Parut', 'Kacang Tanah', 'Pisang',
            ],
        ],
        [
            'supplier' => 'Toko Sembako Awi',
            'keterangan' => 'Belanja sembako dan perlengkapan dapur',
            'offset' => 3,
            'bahan' => [
                'Beras', 'Santan', 'Minyak Goreng', 'Gula Pasir', 'Gula Merah', 'Garam',
                'Kecap Manis', 'Tepung Terigu', 'Tepung Bumbu', 'Pasta Spaghetti',
                'Saus Tomat', 'Saus Sambal', 'Mie Kuning', 'Roti Tawar', 'Selai Coklat',
                'Coklat Bubuk', 'Keju Cheddar', 'Susu Kental Manis', 'Susu UHT', 'Es Batu', 'Kerupuk',
            ],
        ],
        [
            'supplier' => 'Kopi Nusantara Roastery',
            'keterangan' => 'Pasokan biji kopi dan gula aren',
            'offset' => 5,
            'bahan' => ['Kopi Arabika', 'Kopi Robusta', 'Kopi Blend', 'Gula Aren'],
        ],
    ];

    /**
     * Contoh pengeluaran operasional bulan berjalan.
     */
    private const PENGELUARAN = [
        ['offset' => 0, 'kategori' => 'sewa', 'keterangan' => 'Sewa lahan dan bangunan saung bulan ini', 'jumlah' => 1500000],
        ['offset' => 0, 'kategori' => 'gaji', 'keterangan' => 'Gaji staf dapur, kasir, dan pelayan', 'jumlah' => 3500000],
        ['offset' => 2, 'kategori' => 'listrik_air', 'keterangan' => 'Tagihan listrik dan air', 'jumlah' => 450000],
        ['offset' => 4, 'kategori' => 'bahan_bakar', 'keterangan' => 'Gas LPG dan arang untuk dapur', 'jumlah' => 300000],
        ['offset' => 6, 'kategori' => 'perlengkapan', 'keterangan' => 'Perlengkapan kebersihan dan kemasan', 'jumlah' => 275000],
        ['offset' => 8, 'kategori' => 'perawatan', 'keterangan' => 'Perawatan pompa air dan area saung', 'jumlah' => 350000],
        ['offset' => 10, 'kategori' => 'transportasi', 'keterangan' => 'Transportasi belanja dan distribusi', 'jumlah' => 150000],
        ['offset' => 12, 'kategori' => 'lain_lain', 'keterangan' => 'Biaya administrasi dan operasional lain', 'jumlah' => 125000],
    ];

    public function run(): void
    {
        $adminId = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');

        $this->seedBahan();
        $this->seedResep();
        $this->seedPembelian($adminId === null ? null : (int) $adminId);
        $this->seedPengeluaran($adminId === null ? null : (int) $adminId);

        $this->command?->info(sprintf(
            'Data bahan baku siap: %s bahan, %s resep menu, %s pembelian, %s pengeluaran.',
            number_format(BahanBaku::count(), 0, ',', '.'),
            number_format(ResepMenu::count(), 0, ',', '.'),
            number_format(PembelianBahan::count(), 0, ',', '.'),
            number_format(Pengeluaran::count(), 0, ',', '.')
        ));
    }

    private function seedBahan(): void
    {
        foreach (self::BAHAN as $nama => [$satuan, $stokMinimum, $harga, $jumlahAwal]) {
            BahanBaku::firstOrCreate(
                ['nama' => $nama],
                [
                    'satuan' => $satuan,
                    'stok' => 0,
                    'stok_minimum' => $stokMinimum,
                    'harga_rata_rata' => 0,
                    'is_aktif' => true,
                ]
            );
        }
    }

    private function seedResep(): void
    {
        $menu = Menu::query()->get()->keyBy('nama_menu');
        $bahan = BahanBaku::query()->pluck('id', 'nama');
        $dibuat = 0;

        foreach (self::RESEP as $namaMenu => $items) {
            $dataMenu = $menu->get($namaMenu);

            if (! $dataMenu) {
                $this->command?->warn('Resep dilewati, menu tidak ditemukan: '.$namaMenu);

                continue;
            }

            if ($dataMenu->resep()->exists()) {
                continue;
            }

            foreach ($items as $namaBahan => $jumlah) {
                $bahanId = $bahan->get($namaBahan);

                if ($bahanId === null) {
                    $this->command?->warn('Resep '.$namaMenu.' dilewati, bahan tidak ditemukan: '.$namaBahan);

                    continue;
                }

                ResepMenu::create([
                    'menu_id' => $dataMenu->id,
                    'bahan_baku_id' => $bahanId,
                    'jumlah' => $jumlah,
                ]);

                $dibuat++;
            }
        }

        if ($dibuat > 0) {
            $this->command?->info('Resep dibuat untuk '.number_format($dibuat, 0, ',', '.').' baris bahan.');
        }

        $tanpaResep = Menu::query()->doesntHave('resep')->count();

        if ($tanpaResep > 0) {
            $this->command?->warn($tanpaResep.' menu belum memiliki resep.');
        }
    }

    private function seedPembelian(?int $adminId): void
    {
        if (PembelianBahan::query()->exists()) {
            $this->command?->info('Pembelian bahan dilewati: sudah ada data pembelian.');

            return;
        }

        $service = app(BahanStokService::class);
        $mulaiBulan = Carbon::now('Asia/Jakarta')->startOfMonth();

        foreach (self::PEMBELIAN as $batch) {
            $tanggal = $mulaiBulan->copy()->addDays($batch['offset']);
            $waktu = $this->waktuAman($tanggal, 8);
            $items = [];

            foreach ($batch['bahan'] as $nama) {
                $bahan = BahanBaku::query()->where('nama', $nama)->first();

                if (! $bahan) {
                    continue;
                }

                [, , $harga, $jumlahAwal] = self::BAHAN[$nama];

                $items[] = [
                    'bahan_baku_id' => $bahan->id,
                    'jumlah' => $jumlahAwal,
                    'harga_satuan' => $harga,
                ];
            }

            if ($items === []) {
                continue;
            }

            $service->buatPembelian([
                'tanggal' => $waktu->toDateString(),
                'supplier' => $batch['supplier'],
                'keterangan' => $batch['keterangan'],
                'total' => 0,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ], $items, $adminId);
        }
    }

    private function seedPengeluaran(?int $adminId): void
    {
        if (Pengeluaran::query()->exists()) {
            $this->command?->info('Pengeluaran operasional dilewati: sudah ada data pengeluaran.');

            return;
        }

        $mulaiBulan = Carbon::now('Asia/Jakarta')->startOfMonth();

        foreach (self::PENGELUARAN as $baris) {
            $waktu = $this->waktuAman($mulaiBulan->copy()->addDays($baris['offset']), 10);

            Pengeluaran::create([
                'tanggal' => $waktu->toDateString(),
                'kategori' => $baris['kategori'],
                'keterangan' => $baris['keterangan'],
                'jumlah' => $baris['jumlah'],
                'created_by' => $adminId,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ]);
        }
    }

    private function waktuAman(Carbon $tanggal, int $jam): Carbon
    {
        $waktu = $tanggal->copy()->setTime($jam, 0);

        return $waktu->greaterThan(now()) ? now() : $waktu;
    }
}
