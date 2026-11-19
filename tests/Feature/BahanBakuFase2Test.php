<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\BahanStokMutasi;
use App\Models\DetailPesanan;
use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pembayaran;
use App\Models\PembelianBahan;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Models\ResepMenu;
use App\Models\User;
use App\Services\BahanStokService;
use App\Services\PesananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use RuntimeException;
use Tests\TestCase;

class BahanBakuFase2Test extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function kasir(): User
    {
        return User::factory()->create(['role' => 'kasir']);
    }

    private function meja(string $kode = 'LB-70'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-'.strtolower($kode).'-fase2',
            'status_meja' => 'kosong',
        ]);
    }

    private function menu(string $nama = 'Nasi Uji', int $harga = 20000, int $stok = 20): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan '.uniqid(),
            'target_kds' => 'dapur',
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => $nama.' '.uniqid(),
            'slug' => 'menu-uji-'.uniqid(),
            'harga' => $harga,
            'stok_menu' => $stok,
            'is_aktif' => true,
        ]);
    }

    private function bahan(string $nama = 'Beras', string $satuan = 'kg'): BahanBaku
    {
        return BahanBaku::create([
            'nama' => $nama.' '.uniqid(),
            'satuan' => $satuan,
            'stok' => 0,
            'stok_minimum' => 0,
            'harga_rata_rata' => 0,
            'is_aktif' => true,
        ]);
    }

    private function catatPembelian(
        User $admin,
        array $items,
        string $tanggal = '2026-11-10',
        string $supplier = 'Pasar Induk',
    ): PembelianBahan {
        $this->actingAs($admin)->post(route('admin.arus-kas.pembelian.simpan'), [
            'tanggal' => $tanggal,
            'supplier' => $supplier,
            'keterangan' => 'Pembelian uji',
            'total' => 1,
            'items' => $items,
        ])->assertRedirect()->assertSessionHas('sukses');

        return PembelianBahan::query()->latest('id')->firstOrFail();
    }

    private function isiStok(BahanBaku $bahan, float $jumlah, ?int $penggunaId = null): void
    {
        app(BahanStokService::class)->sesuaikanStok($bahan, $jumlah, 'Stok uji', $penggunaId);
        $bahan->refresh();
    }

    private function pesananLunasDenganDetail(string $kode, array $detail, string $waktuBayar): Pesanan
    {
        $total = collect($detail)->sum(fn (array $baris) => $baris['subtotal']);

        $pesanan = Pesanan::create([
            'kode_pesanan' => $kode,
            'meja_id' => $this->meja()->id,
            'total_bayar' => $total,
            'status_pesanan' => Pesanan::STATUS_SELESAI,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'metode_pembayaran' => 'tunai',
            'waktu_pesan' => Carbon::parse($waktuBayar, 'Asia/Jakarta')->utc(),
        ]);

        foreach ($detail as $baris) {
            DetailPesanan::create([
                'pesanan_id' => $pesanan->id,
                'menu_id' => $baris['menu_id'],
                'jumlah' => $baris['jumlah'],
                'harga_satuan' => $baris['harga_satuan'],
                'subtotal' => $baris['subtotal'],
                'status_item' => 'menunggu',
            ]);
        }

        Pembayaran::create([
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'tunai',
            'status_pembayaran' => 'lunas',
            'jumlah_bayar' => $total,
            'waktu_bayar' => Carbon::parse($waktuBayar, 'Asia/Jakarta')->utc(),
        ]);

        return $pesanan;
    }

    public function test_admin_dapat_menambah_mengubah_dan_menghapus_bahan_baku(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.bahan-baku.simpan'), [
            'nama' => 'Beras Pandan',
            'satuan' => 'kg',
            'stok_minimum' => 3,
            'stok_awal' => 12.5,
            'is_aktif' => 1,
        ])->assertRedirect()->assertSessionHas('sukses');

        $bahan = BahanBaku::firstOrFail();
        $this->assertSame('Beras Pandan', $bahan->nama);
        $this->assertSame('kg', $bahan->satuan);
        $this->assertEquals(12.5, (float) $bahan->stok);
        $this->assertTrue($bahan->is_aktif);
        $this->assertDatabaseHas('bahan_stok_mutasi', [
            'bahan_baku_id' => $bahan->id,
            'jenis' => 'penyesuaian',
            'keterangan' => 'Stok awal bahan baku',
        ]);

        $this->actingAs($admin)->get(route('admin.bahan-baku'))
            ->assertOk()
            ->assertSee('Beras Pandan')
            ->assertSee('Resep Menu & HPP Standar', false);

        $this->actingAs($admin)->put(route('admin.bahan-baku.ubah', $bahan), [
            'nama' => 'Beras Pandan Wangi',
            'satuan' => 'kg',
            'stok_minimum' => 4,
            'is_aktif' => 0,
        ])->assertRedirect()->assertSessionHas('sukses');

        $bahan->refresh();
        $this->assertSame('Beras Pandan Wangi', $bahan->nama);
        $this->assertEquals(4, (float) $bahan->stok_minimum);
        $this->assertFalse($bahan->is_aktif);

        $tanpaRiwayat = BahanBaku::create([
            'nama' => 'Garam Meja',
            'satuan' => 'kg',
            'stok' => 0,
            'stok_minimum' => 0,
            'harga_rata_rata' => 0,
            'is_aktif' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.bahan-baku.hapus', $tanpaRiwayat))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertDatabaseMissing('bahan_baku', ['id' => $tanpaRiwayat->id]);

        $this->actingAs($admin)
            ->delete(route('admin.bahan-baku.hapus', $bahan))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bahan_baku', ['id' => $bahan->id]);
    }

    public function test_validasi_bahan_menolak_nama_ganda_dan_satuan_kosong(): void
    {
        $admin = $this->admin();
        BahanBaku::create([
            'nama' => 'Minyak Goreng',
            'satuan' => 'liter',
            'stok' => 0,
            'stok_minimum' => 0,
            'harga_rata_rata' => 0,
            'is_aktif' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.bahan-baku.simpan'), [
            'nama' => 'Minyak Goreng',
            'satuan' => '',
            'stok_minimum' => -1,
        ])->assertSessionHasErrors(['nama', 'satuan', 'stok_minimum']);

        $this->assertDatabaseCount('bahan_baku', 1);
    }

    public function test_pembelian_berdetail_menambah_stok_dan_menghitung_harga_rata_rata_tertimbang(): void
    {
        $admin = $this->admin();
        $beras = $this->bahan('Beras');
        $telur = $this->bahan('Telur', 'butir');

        $pembelian = $this->catatPembelian($admin, [
            ['bahan_baku_id' => $beras->id, 'jumlah' => 10, 'harga_satuan' => 5000],
            ['bahan_baku_id' => $telur->id, 'jumlah' => 30, 'harga_satuan' => 2000],
        ]);

        $this->assertSame('110000.00', (string) $pembelian->total);
        $this->assertCount(2, $pembelian->detail);
        $this->assertDatabaseCount('pembelian_bahan_detail', 2);

        $beras->refresh();
        $telur->refresh();
        $this->assertEquals(10, (float) $beras->stok);
        $this->assertEquals(5000, (float) $beras->harga_rata_rata);
        $this->assertEquals(30, (float) $telur->stok);
        $this->assertEquals(2000, (float) $telur->harga_rata_rata);

        $this->catatPembelian($admin, [
            ['bahan_baku_id' => $beras->id, 'jumlah' => 10, 'harga_satuan' => 7000],
        ], '2026-11-11', 'Pasar Baru');

        $beras->refresh();
        $this->assertEquals(20, (float) $beras->stok);
        $this->assertEquals(6000, (float) $beras->harga_rata_rata);
        $this->assertDatabaseCount('bahan_stok_mutasi', 3);
    }

    public function test_pembelian_tanpa_rincian_tetap_tersimpan_sebagai_catatan_total_manual(): void
    {
        $admin = $this->admin();
        $beras = $this->bahan('Beras');

        $this->actingAs($admin)->post(route('admin.arus-kas.pembelian.simpan'), [
            'tanggal' => '2026-11-10',
            'supplier' => 'Warung Lama',
            'keterangan' => 'Catatan lama tanpa rincian',
            'total' => 45000,
        ])->assertRedirect()->assertSessionHas('sukses');

        $pembelian = PembelianBahan::firstOrFail();
        $this->assertSame('45000.00', (string) $pembelian->total);
        $this->assertDatabaseCount('pembelian_bahan_detail', 0);

        $beras->refresh();
        $this->assertEquals(0, (float) $beras->stok);
        $this->assertEquals(0, (float) $beras->harga_rata_rata);
    }

    public function test_ubah_dan_hapus_pembelian_menulis_ulang_ledger_stok(): void
    {
        $admin = $this->admin();
        $beras = $this->bahan('Beras');
        $pembelian = $this->catatPembelian($admin, [
            ['bahan_baku_id' => $beras->id, 'jumlah' => 10, 'harga_satuan' => 5000],
        ]);

        $this->actingAs($admin)->put(route('admin.arus-kas.pembelian.ubah', $pembelian), [
            'tanggal' => '2026-11-11',
            'supplier' => 'Pasar Baru',
            'keterangan' => 'Revisi pembelian',
            'total' => 1,
            'items' => [
                ['bahan_baku_id' => $beras->id, 'jumlah' => 6, 'harga_satuan' => 5000],
            ],
        ])->assertRedirect()->assertSessionHas('sukses');

        $beras->refresh();
        $pembelian->refresh();
        $this->assertEquals(6, (float) $beras->stok);
        $this->assertEquals(5000, (float) $beras->harga_rata_rata);
        $this->assertSame('30000.00', (string) $pembelian->total);
        $this->assertDatabaseCount('pembelian_bahan_detail', 1);
        $this->assertDatabaseCount('bahan_stok_mutasi', 1);

        $this->actingAs($admin)
            ->delete(route('admin.arus-kas.pembelian.hapus', $pembelian))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $beras->refresh();
        $this->assertEquals(0, (float) $beras->stok);
        $this->assertEquals(0, (float) $beras->harga_rata_rata);
        $this->assertDatabaseCount('pembelian_bahan', 0);
        $this->assertDatabaseCount('pembelian_bahan_detail', 0);
        $this->assertDatabaseCount('bahan_stok_mutasi', 0);
    }

    public function test_ubah_pembelian_menghapus_bahan_lama_dari_ledger_stok(): void
    {
        $admin = $this->admin();
        $beras = $this->bahan('Beras');
        $telur = $this->bahan('Telur', 'butir');
        $pembelian = $this->catatPembelian($admin, [
            ['bahan_baku_id' => $beras->id, 'jumlah' => 10, 'harga_satuan' => 5000],
            ['bahan_baku_id' => $telur->id, 'jumlah' => 30, 'harga_satuan' => 2000],
        ]);

        $this->actingAs($admin)->put(route('admin.arus-kas.pembelian.ubah', $pembelian), [
            'tanggal' => '2026-11-11',
            'supplier' => 'Pasar Baru',
            'keterangan' => 'Telur batal dibeli',
            'total' => 1,
            'items' => [
                ['bahan_baku_id' => $beras->id, 'jumlah' => 10, 'harga_satuan' => 5000],
            ],
        ])->assertRedirect()->assertSessionHas('sukses');

        $beras->refresh();
        $telur->refresh();
        $this->assertEquals(10, (float) $beras->stok);
        $this->assertEquals(5000, (float) $beras->harga_rata_rata);
        $this->assertEquals(0, (float) $telur->stok);
        $this->assertEquals(0, (float) $telur->harga_rata_rata);
        $this->assertDatabaseCount('pembelian_bahan_detail', 1);
        $this->assertSame(0, $telur->mutasi()->count());
    }

    public function test_ubah_atau_hapus_pembelian_ditolak_bila_membuat_stok_negatif(): void
    {
        $admin = $this->admin();
        $beras = $this->bahan('Beras');
        $pembelian = $this->catatPembelian($admin, [
            ['bahan_baku_id' => $beras->id, 'jumlah' => 10, 'harga_satuan' => 5000],
        ]);

        $this->isiStok($beras, 4, $admin->id);

        $this->actingAs($admin)->put(route('admin.arus-kas.pembelian.ubah', $pembelian), [
            'tanggal' => '2026-11-11',
            'supplier' => 'Pasar Baru',
            'keterangan' => 'Stok sudah terpakai',
            'total' => 1,
            'items' => [
                ['bahan_baku_id' => $beras->id, 'jumlah' => 3, 'harga_satuan' => 5000],
            ],
        ])->assertRedirect()->assertSessionHas('error');

        $beras->refresh();
        $pembelian->refresh();
        $this->assertEquals(4, (float) $beras->stok);
        $this->assertEquals(10, (float) $pembelian->detail()->sum('jumlah'));

        $this->actingAs($admin)
            ->delete(route('admin.arus-kas.pembelian.hapus', $pembelian))
            ->assertRedirect()
            ->assertSessionHas('error');

        $beras->refresh();
        $this->assertEquals(4, (float) $beras->stok);
        $this->assertDatabaseHas('pembelian_bahan', ['id' => $pembelian->id]);
    }

    public function test_penyesuaian_stok_dan_riwayat_mutasi_bahan(): void
    {
        $admin = $this->admin();
        $gula = $this->bahan('Gula Pasir');

        $this->actingAs($admin)->post(route('admin.bahan-baku.stok', $gula), [
            'stok_baru' => 7.25,
            'keterangan' => 'Stock opname akhir bulan',
        ])->assertRedirect()->assertSessionHas('sukses');

        $gula->refresh();
        $this->assertEquals(7.25, (float) $gula->stok);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.bahan-baku.mutasi', $gula))
            ->assertOk()
            ->assertJsonFragment([
                'jenis' => 'penyesuaian',
                'keterangan' => 'Stock opname akhir bulan',
                'pencatat' => $admin->nama,
            ]);

        $this->assertEquals(7.25, (float) $response->json('bahan.stok'));

        $this->actingAs($admin)->post(route('admin.bahan-baku.stok', $gula), [
            'stok_baru' => -1,
        ])->assertSessionHasErrors('stok_baru');

        $gula->refresh();
        $this->assertEquals(7.25, (float) $gula->stok);
    }

    public function test_resep_menggabungkan_bahan_ganda_dan_menghasilkan_hpp_satuan(): void
    {
        $admin = $this->admin();
        $beras = $this->bahan('Beras');
        $menu = $this->menu('Nasi Resep', 20000);

        $this->catatPembelian($admin, [
            ['bahan_baku_id' => $beras->id, 'jumlah' => 1, 'harga_satuan' => 10000],
        ]);
        $this->catatPembelian($admin, [
            ['bahan_baku_id' => $beras->id, 'jumlah' => 1, 'harga_satuan' => 20000],
        ], '2026-11-11', 'Pasar Baru');

        $this->actingAs($admin)->put(route('admin.bahan-baku.resep', $menu), [
            'items' => [
                ['bahan_baku_id' => $beras->id, 'jumlah' => 0.2],
                ['bahan_baku_id' => $beras->id, 'jumlah' => 0.3],
            ],
        ])->assertRedirect()->assertSessionHas('sukses');

        $this->assertDatabaseCount('resep_menu', 1);
        $resep = ResepMenu::firstOrFail();
        $this->assertEquals(0.5, (float) $resep->jumlah);

        $menu->load('resep.bahan');
        $menu->refresh()->load('resep.bahan');
        $this->assertEquals(7500, $menu->hppSatuan());

        $this->actingAs($admin)->get(route('admin.bahan-baku'))
            ->assertOk()
            ->assertSee($menu->nama_menu)
            ->assertSee('Rp 7.500');

        $this->actingAs($admin)->put(route('admin.bahan-baku.resep', $menu), [
            'items' => [],
        ])->assertRedirect()->assertSessionHas('sukses');

        $this->assertDatabaseCount('resep_menu', 0);
    }

    public function test_validasi_resep_menolak_bahan_yang_tidak_ada(): void
    {
        $admin = $this->admin();
        $menu = $this->menu('Menu Validasi', 15000);

        $this->actingAs($admin)->put(route('admin.bahan-baku.resep', $menu), [
            'items' => [
                ['bahan_baku_id' => 99999, 'jumlah' => 1],
            ],
        ])->assertSessionHasErrors('items.0.bahan_baku_id');

        $this->assertDatabaseCount('resep_menu', 0);
    }

    public function test_checkout_memotong_stok_bahan_sesuai_resep_dan_pembatalan_mengembalikannya(): void
    {
        $admin = $this->admin();
        $kasir = $this->kasir();
        $beras = $this->bahan('Beras');
        $menu = $this->menu('Nasi Goreng', 25000);
        $meja = $this->meja();

        $this->catatPembelian($admin, [
            ['bahan_baku_id' => $beras->id, 'jumlah' => 10, 'harga_satuan' => 5000],
        ]);

        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $beras->id,
            'jumlah' => 0.5,
        ]);

        $this->actingAs($kasir);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 2],
            [],
            ['asal' => 'kasir', 'metode' => 'tunai'],
        );

        $beras->refresh();
        $menu->refresh();
        $this->assertEquals(9, (float) $beras->stok);
        $this->assertSame(18, (int) $menu->stok_menu);
        $this->assertDatabaseHas('bahan_stok_mutasi', [
            'bahan_baku_id' => $beras->id,
            'pesanan_id' => $pesanan->id,
            'jenis' => 'penjualan',
        ]);

        app(PesananService::class)->batalkan($pesanan, $kasir->id, 'Batal uji bahan');

        $beras->refresh();
        $menu->refresh();
        $pesanan->refresh();
        $this->assertSame(Pesanan::STATUS_DIBATALKAN, $pesanan->status_pesanan);
        $this->assertEquals(10, (float) $beras->stok);
        $this->assertSame(20, (int) $menu->stok_menu);
        $this->assertDatabaseHas('bahan_stok_mutasi', [
            'bahan_baku_id' => $beras->id,
            'pesanan_id' => $pesanan->id,
            'jenis' => 'pembatalan',
        ]);

        app(PesananService::class)->batalkan($pesanan, $kasir->id, 'Batal uji bahan lagi');

        $beras->refresh();
        $this->assertEquals(10, (float) $beras->stok);
        $this->assertSame(1, BahanStokMutasi::query()
            ->where('pesanan_id', $pesanan->id)
            ->where('jenis', 'pembatalan')
            ->count());
    }

    public function test_checkout_ditolak_dan_rollback_bila_stok_bahan_kurang(): void
    {
        $admin = $this->admin();
        $kasir = $this->kasir();
        $beras = $this->bahan('Beras');
        $menu = $this->menu('Nasi Porsi Besar', 30000);
        $meja = $this->meja();

        $this->catatPembelian($admin, [
            ['bahan_baku_id' => $beras->id, 'jumlah' => 1, 'harga_satuan' => 12000],
        ]);

        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $beras->id,
            'jumlah' => 1,
        ]);

        $this->actingAs($kasir);

        try {
            app(PesananService::class)->buat(
                $meja,
                [$menu->id => 2],
                [],
                ['asal' => 'kasir', 'metode' => 'tunai'],
            );

            $this->fail('Checkout seharusnya ditolak karena stok bahan kurang.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Stok bahan '.$beras->nama, $exception->getMessage());
        }

        $beras->refresh();
        $menu->refresh();
        $meja->refresh();
        $this->assertEquals(1, (float) $beras->stok);
        $this->assertSame(20, (int) $menu->stok_menu);
        $this->assertSame('kosong', $meja->status_meja);
        $this->assertDatabaseCount('pesanan', 0);
        $this->assertDatabaseCount('stok_mutasi', 0);
        $this->assertSame(1, BahanStokMutasi::query()->count());
    }

    public function test_menu_tanpa_resep_tidak_memotong_stok_bahan(): void
    {
        $kasir = $this->kasir();
        $menu = $this->menu('Teh Tanpa Resep', 8000);
        $meja = $this->meja();

        $this->actingAs($kasir);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'kasir', 'metode' => 'tunai'],
        );

        $this->assertNotNull($pesanan->id);
        $this->assertDatabaseCount('bahan_stok_mutasi', 0);
    }

    public function test_laporan_hpp_menampilkan_hpp_laba_kotor_dan_margin_per_menu(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));
        Pengaturan::simpanBanyak(['nama_resto' => 'Saung Situ Awi']);

        $admin = $this->admin();
        $beras = $this->bahan('Beras');
        $menuBerresep = $this->menu('Nasi HPP', 20000);
        $menuTanpaResep = $this->menu('Teh HPP', 10000);

        $this->catatPembelian($admin, [
            ['bahan_baku_id' => $beras->id, 'jumlah' => 10, 'harga_satuan' => 5000],
        ]);

        ResepMenu::create([
            'menu_id' => $menuBerresep->id,
            'bahan_baku_id' => $beras->id,
            'jumlah' => 1,
        ]);

        $this->pesananLunasDenganDetail('AWI-HPP-1', [
            [
                'menu_id' => $menuBerresep->id,
                'jumlah' => 2,
                'harga_satuan' => 20000,
                'subtotal' => 40000,
            ],
            [
                'menu_id' => $menuTanpaResep->id,
                'jumlah' => 1,
                'harga_satuan' => 10000,
                'subtotal' => 10000,
            ],
        ], '2026-11-10 08:00:00');

        $response = $this->actingAs($admin)->get(route('admin.arus-kas', [
            'start' => '2026-11-10',
            'end' => '2026-11-10',
        ]));

        $response->assertOk()
            ->assertSee('Laba Kotor')
            ->assertSee($menuBerresep->nama_menu)
            ->assertSee($menuTanpaResep->nama_menu)
            ->assertSee('75,0%')
            ->assertSee('80,0%');

        $csv = $this->actingAs($admin)->get(route('admin.arus-kas.ekspor', [
            'format' => 'csv',
            'start' => '2026-11-10',
            'end' => '2026-11-10',
        ]))->assertOk()->streamedContent();

        $this->assertStringContainsString('HPP & LABA KOTOR PER MENU', $csv);
        $this->assertStringContainsString($menuBerresep->nama_menu, $csv);
        $this->assertStringContainsString('TOTAL', $csv);

        $xls = $this->actingAs($admin)->get(route('admin.arus-kas.ekspor', [
            'format' => 'xls',
            'start' => '2026-11-10',
            'end' => '2026-11-10',
        ]))->assertOk()->streamedContent();

        $this->assertStringContainsString('HPP &amp; LABA KOTOR PER MENU', $xls);
        $this->assertStringContainsString($menuBerresep->nama_menu, $xls);

        $this->actingAs($admin)->get(route('admin.arus-kas.cetak', [
            'start' => '2026-11-10',
            'end' => '2026-11-10',
        ]))->assertOk()
            ->assertSee('Laba Kotor per Menu')
            ->assertSee($menuBerresep->nama_menu)
            ->assertSee('Rp 10.000');
    }

    public function test_pegawai_non_admin_tidak_bisa_mengakses_bahan_baku(): void
    {
        $kasir = $this->kasir();

        $this->actingAs($kasir)->get(route('admin.bahan-baku'))->assertStatus(403);

        $this->actingAs($kasir)->post(route('admin.bahan-baku.simpan'), [
            'nama' => 'Bahan Ilegal',
            'satuan' => 'kg',
        ])->assertStatus(403);

        $this->assertDatabaseCount('bahan_baku', 0);
    }
}
