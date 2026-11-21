<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\BahanStokMutasi;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\KeranjangItem;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\ResepMenu;
use App\Models\SesiMeja;
use App\Models\StokMutasi;
use App\Services\BahanStokService;
use App\Services\KeranjangService;
use App\Services\PesananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class KeranjangStokTest extends TestCase
{
    use RefreshDatabase;

    private function meja(string $kode = 'LB-K1'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-'.strtolower($kode).'-keranjang',
            'status_meja' => 'kosong',
        ]);
    }

    private function menu(int $stok = 10, int $harga = 20000): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan '.uniqid(),
            'target_kds' => 'dapur',
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Menu Keranjang '.uniqid(),
            'slug' => 'menu-keranjang-'.uniqid(),
            'harga' => $harga,
            'stok_menu' => $stok,
            'is_aktif' => true,
        ]);
    }

    private function bahanDenganStok(float $stok = 20): BahanBaku
    {
        $bahan = BahanBaku::create([
            'nama' => 'Bahan Uji '.uniqid(),
            'satuan' => 'kg',
            'stok' => 0,
            'stok_minimum' => 0,
            'harga_rata_rata' => 5000,
            'is_aktif' => true,
        ]);

        app(BahanStokService::class)->sesuaikanStok($bahan, $stok, 'Stok awal uji');

        return $bahan->refresh();
    }

    public function test_item_keranjang_langsung_memotong_stok_menu_dan_bahan(): void
    {
        $meja = $this->meja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $menu = $this->menu(10);
        $bahan = $this->bahanDenganStok(20);

        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahan->id,
            'jumlah' => 0.5,
        ]);

        $service = app(KeranjangService::class);

        $keranjang = $service->setItem($meja, $sesi, 'perangkat-a', $menu->id, 2);

        $this->assertNotNull($keranjang);
        $this->assertSame(8, (int) $menu->fresh()->stok_menu);
        $this->assertEquals(19, (float) $bahan->fresh()->stok);
        $this->assertSame(2, (int) $keranjang->items()->firstOrFail()->jumlah);
        $this->assertSame(0, StokMutasi::query()->count());

        $reservasi = BahanStokMutasi::query()->where('jenis', 'reservasi')->firstOrFail();
        $this->assertEquals(-1, (float) $reservasi->jumlah_delta);
        $this->assertEquals(19, (float) $reservasi->stok_akhir);

        $service->setItem($meja, $sesi, 'perangkat-a', $menu->id, 3);

        $this->assertSame(7, (int) $menu->fresh()->stok_menu);
        $this->assertEquals(18.5, (float) $bahan->fresh()->stok);

        $service->setItem($meja, $sesi, 'perangkat-a', $menu->id, 1);

        $this->assertSame(9, (int) $menu->fresh()->stok_menu);
        $this->assertEquals(19.5, (float) $bahan->fresh()->stok);

        $service->setItem($meja, $sesi, 'perangkat-a', $menu->id, 0);

        $this->assertSame(10, (int) $menu->fresh()->stok_menu);
        $this->assertEquals(20, (float) $bahan->fresh()->stok);
        $this->assertSame(0, Keranjang::query()->count());
        $this->assertSame(0, KeranjangItem::query()->count());
        $this->assertSame(0, BahanStokMutasi::query()->where('jenis', 'reservasi')->count());
    }

    public function test_dua_perangkat_memperebutkan_stok_terakhir_hanya_satu_yang_dapat_checkout(): void
    {
        $menu = $this->menu(1, 25000);
        $mejaA = $this->meja('LB-K2');
        $mejaB = $this->meja('LB-K3');
        $sesiA = SesiMeja::aktifUntuk($mejaA);
        $sesiB = SesiMeja::aktifUntuk($mejaB);
        $service = app(KeranjangService::class);

        $keranjangA = $service->setItem($mejaA, $sesiA, 'perangkat-a', $menu->id, 1);

        $this->assertNotNull($keranjangA);
        $this->assertSame(0, (int) $menu->fresh()->stok_menu);

        try {
            $service->setItem($mejaB, $sesiB, 'perangkat-b', $menu->id, 1);
            $this->fail('Perangkat kedua seharusnya ditolak karena stok habis.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Stok '.$menu->nama_menu, $exception->getMessage());
        }

        $this->assertSame(0, (int) $menu->fresh()->stok_menu);
        $this->assertSame(1, Keranjang::query()->count());
        $this->assertSame(1, KeranjangItem::query()->count());

        $pesanan = app(PesananService::class)->buat(
            $mejaA,
            [$menu->id => 1],
            [],
            [
                'asal' => 'emenu',
                'metode' => 'tunai',
                'keranjang_id' => $keranjangA->id,
            ]
        );

        $this->assertNotNull($pesanan->id);
        $this->assertSame(1, Pesanan::query()->count());
        $this->assertSame(0, (int) $menu->fresh()->stok_menu);
        $this->assertSame(0, Keranjang::query()->count());

        try {
            app(PesananService::class)->buat(
                $mejaB,
                [$menu->id => 1],
                [],
                ['asal' => 'emenu', 'metode' => 'tunai']
            );
            $this->fail('Checkout perangkat kedua seharusnya ditolak.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Stok '.$menu->nama_menu, $exception->getMessage());
        }

        $this->assertSame(1, Pesanan::query()->count());
        $this->assertSame(1, StokMutasi::query()->where('jenis', 'penjualan')->count());
    }

    public function test_checkout_memakai_reservasi_tanpa_memotong_stok_dua_kali_dan_pembatalan_mengembalikan(): void
    {
        $meja = $this->meja('LB-K4');
        $sesi = SesiMeja::aktifUntuk($meja);
        $menu = $this->menu(10);
        $bahan = $this->bahanDenganStok(20);

        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahan->id,
            'jumlah' => 0.5,
        ]);

        $keranjang = app(KeranjangService::class)->setItem($meja, $sesi, 'perangkat-a', $menu->id, 2);

        $this->assertSame(8, (int) $menu->fresh()->stok_menu);
        $this->assertEquals(19, (float) $bahan->fresh()->stok);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 2],
            [],
            [
                'asal' => 'emenu',
                'metode' => 'tunai',
                'keranjang_id' => $keranjang->id,
            ]
        );

        $this->assertSame(8, (int) $menu->fresh()->stok_menu);
        $this->assertEquals(19, (float) $bahan->fresh()->stok);
        $this->assertSame(0, Keranjang::query()->count());

        $mutasiMenu = StokMutasi::query()->where('jenis', 'penjualan')->firstOrFail();
        $this->assertSame($pesanan->id, (int) $mutasiMenu->pesanan_id);
        $this->assertSame(-2, (int) $mutasiMenu->jumlah_delta);
        $this->assertSame(8, (int) $mutasiMenu->stok_akhir);

        $mutasiBahan = BahanStokMutasi::query()->where('jenis', 'penjualan')->firstOrFail();
        $this->assertSame($pesanan->id, (int) $mutasiBahan->pesanan_id);
        $this->assertEquals(-1, (float) $mutasiBahan->jumlah_delta);

        app(PesananService::class)->batalkan($pesanan, null, 'Batal uji keranjang');

        $this->assertSame(10, (int) $menu->fresh()->stok_menu);
        $this->assertEquals(20, (float) $bahan->fresh()->stok);
        $this->assertSame(1, StokMutasi::query()->where('jenis', 'pembatalan')->count());
        $this->assertSame(1, BahanStokMutasi::query()->where('jenis', 'pembatalan')->count());
    }

    public function test_checkout_dengan_jumlah_berbeda_dari_reservasi_ditolak_tanpa_mengubah_stok(): void
    {
        $meja = $this->meja('LB-K5');
        $sesi = SesiMeja::aktifUntuk($meja);
        $menu = $this->menu(10);
        $keranjang = app(KeranjangService::class)->setItem($meja, $sesi, 'perangkat-a', $menu->id, 1);

        try {
            app(PesananService::class)->buat(
                $meja,
                [$menu->id => 2],
                [],
                [
                    'asal' => 'emenu',
                    'metode' => 'tunai',
                    'keranjang_id' => $keranjang->id,
                ]
            );
            $this->fail('Checkout dengan jumlah berbeda seharusnya ditolak.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Isi keranjang berubah', $exception->getMessage());
        }

        $this->assertSame(9, (int) $menu->fresh()->stok_menu);
        $this->assertSame(0, Pesanan::query()->count());
        $this->assertSame(1, Keranjang::query()->count());
        $this->assertSame(1, KeranjangItem::query()->count());
        $this->assertSame(0, StokMutasi::query()->count());
    }

    public function test_keranjang_kedaluwarsa_mengembalikan_stok_menu_dan_bahan(): void
    {
        $meja = $this->meja('LB-K6');
        $sesi = SesiMeja::aktifUntuk($meja);
        $menu = $this->menu(10);
        $bahan = $this->bahanDenganStok(20);

        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahan->id,
            'jumlah' => 1,
        ]);

        app(KeranjangService::class)->setItem($meja, $sesi, 'perangkat-a', $menu->id, 2);

        $this->assertSame(8, (int) $menu->fresh()->stok_menu);
        $this->assertEquals(18, (float) $bahan->fresh()->stok);

        $this->travel(31)->minutes();

        $this->assertSame(1, app(KeranjangService::class)->bersihkanKedaluwarsa());
        $this->assertSame(10, (int) $menu->fresh()->stok_menu);
        $this->assertEquals(20, (float) $bahan->fresh()->stok);
        $this->assertSame(0, Keranjang::query()->count());
        $this->assertSame(0, BahanStokMutasi::query()->where('jenis', 'reservasi')->count());

        $this->travelBack();
    }

    public function test_endpoint_keranjang_menyimpan_reservasi_dan_checkout_memakainya(): void
    {
        $meja = $this->meja('LB-K7');
        $menu = $this->menu(2, 15000);
        $bahan = $this->bahanDenganStok(10);

        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahan->id,
            'jumlah' => 1,
        ]);

        $sesi = SesiMeja::aktifUntuk($meja);

        $this->withSession([
            'sesi_meja_'.$meja->id => $sesi->id,
            'perangkat_pelanggan' => 'perangkat-http-01',
        ]);

        $this->postJson(route('emenu.keranjang.simpan', $meja->id), [
            'menu_id' => $menu->id,
            'jumlah' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('items.0.qty', 1)
            ->assertJsonPath('menu.stock', 2);

        $this->assertSame(1, (int) $menu->fresh()->stok_menu);
        $this->assertEquals(9, (float) $bahan->fresh()->stok);

        $this->post(route('emenu.checkout', $meja->id), [
            'jumlah' => [$menu->id => 1],
            'metode' => 'tunai',
            'nama_pelanggan' => 'Pelanggan Keranjang',
        ])->assertRedirect();

        $this->assertSame(1, Pesanan::query()->count());
        $this->assertSame(1, (int) $menu->fresh()->stok_menu);
        $this->assertEquals(9, (float) $bahan->fresh()->stok);
        $this->assertSame(0, Keranjang::query()->count());
        $this->assertSame(1, StokMutasi::query()->where('jenis', 'penjualan')->count());
        $this->assertSame(1, BahanStokMutasi::query()->where('jenis', 'penjualan')->count());
    }
}
