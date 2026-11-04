<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class IotLcdFaseTest extends TestCase
{
    use RefreshDatabase;

    private int $urutan = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.iot.token', 'token-iot-uji');
    }

    private function meja(string $kode = 'LB-01'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'QR-' . $kode . '-' . Str::random(20),
            'status_meja' => 'kosong',
        ]);
    }

    private function menu(): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori ' . Str::random(6),
            'target_kds' => 'dapur',
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Menu Uji ' . Str::random(5),
            'slug' => 'menu-uji-' . Str::lower(Str::random(6)),
            'harga' => 25000,
            'stok_menu' => 20,
            'is_aktif' => true,
        ]);
    }

    private function pesanan(
        MejaMakan $meja,
        Menu $menu,
        string $metode,
        string $pembayaran,
        string $status = Pesanan::STATUS_MENUNGGU,
    ): Pesanan {
        $this->urutan++;

        $pesanan = Pesanan::create([
            'kode_pesanan' => 'SW-LCD-' . $this->urutan . '-' . Str::upper(Str::random(4)),
            'meja_id' => $meja->id,
            'total_bayar' => 25000,
            'metode_pembayaran' => $metode,
            'status_pesanan' => $status,
            'status_pembayaran' => $pembayaran,
            'waktu_pesan' => now(),
        ]);

        $pesanan->detailPesanan()->create([
            'menu_id' => $menu->id,
            'jumlah' => 1,
            'harga_satuan' => 25000,
            'subtotal' => 25000,
            'status_item' => 'menunggu',
        ]);

        return $pesanan->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function dataMeja(MejaMakan $meja): array
    {
        return $this->withHeader('X-IoT-Token', 'token-iot-uji')
            ->getJson('/api/iot/meja/' . $meja->kode_meja)
            ->assertOk()
            ->json('data');
    }

    public function test_fase_standby_saat_sesi_belum_berisi_pesanan(): void
    {
        $meja = $this->meja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $data = $this->dataMeja($meja);

        $this->assertSame('standby', $data['fase']);
        $this->assertNull($data['metode_pembayaran']);
        $this->assertFalse($data['ada_pesanan']);
        $this->assertFalse($data['semua_lunas']);
        $this->assertSame($sesi->kode_unik, $data['kode_sesi']);
    }

    public function test_pesanan_terbatal_tidak_membuat_lcd_masuk_fase_pembayaran(): void
    {
        $meja = $this->meja();
        SesiMeja::aktifUntuk($meja);
        $menu = $this->menu();

        $this->pesanan(
            $meja,
            $menu,
            'tunai',
            Pesanan::PAYMENT_BELUM_BAYAR,
            Pesanan::STATUS_DIBATALKAN
        );

        $data = $this->dataMeja($meja);

        $this->assertSame('standby', $data['fase']);
        $this->assertFalse($data['ada_pesanan']);
        $this->assertSame(0, $data['jumlah_pesanan']);
    }

    public function test_fase_pembayaran_tunai_menampilkan_metode_tunai(): void
    {
        $meja = $this->meja();
        SesiMeja::aktifUntuk($meja);
        $menu = $this->menu();

        $this->pesanan($meja, $menu, 'tunai', Pesanan::PAYMENT_BELUM_BAYAR);

        $data = $this->dataMeja($meja);

        $this->assertSame('pembayaran', $data['fase']);
        $this->assertSame('tunai', $data['metode_pembayaran']);
        $this->assertTrue($data['ada_pesanan']);
        $this->assertFalse($data['semua_lunas']);
    }

    public function test_fase_pembayaran_qris_menampilkan_metode_qris(): void
    {
        $meja = $this->meja();
        SesiMeja::aktifUntuk($meja);
        $menu = $this->menu();

        $this->pesanan($meja, $menu, 'qris', 'pending_pembayaran');

        $data = $this->dataMeja($meja);

        $this->assertSame('pembayaran', $data['fase']);
        $this->assertSame('qris', $data['metode_pembayaran']);
    }

    public function test_metode_campuran_ditandai_campuran_dan_hanya_pesanan_belum_lunas_yang_dihitung(): void
    {
        $meja = $this->meja();
        SesiMeja::aktifUntuk($meja);
        $menu = $this->menu();

        $this->pesanan($meja, $menu, 'tunai', Pesanan::PAYMENT_SUDAH_BAYAR);
        $this->pesanan($meja, $menu, 'tunai', Pesanan::PAYMENT_BELUM_BAYAR);
        $this->pesanan($meja, $menu, 'qris', 'pending_pembayaran');

        $data = $this->dataMeja($meja);

        $this->assertSame('pembayaran', $data['fase']);
        $this->assertSame('campuran', $data['metode_pembayaran']);
        $this->assertFalse($data['semua_lunas']);
    }

    public function test_fase_lunas_saat_semua_pesanan_sudah_dibayar(): void
    {
        $meja = $this->meja();
        SesiMeja::aktifUntuk($meja);
        $menu = $this->menu();

        $this->pesanan($meja, $menu, 'tunai', Pesanan::PAYMENT_SUDAH_BAYAR);
        $this->pesanan($meja, $menu, 'qris', Pesanan::PAYMENT_SUDAH_BAYAR);

        $data = $this->dataMeja($meja);

        $this->assertSame('lunas', $data['fase']);
        $this->assertTrue($data['ada_pesanan']);
        $this->assertTrue($data['semua_lunas']);
        $this->assertSame(50000.0, (float) $data['total_bayar']);
        $this->assertCount(2, $data['pesanan']);
    }

    public function test_pesanan_selesai_tidak_lagi_dihitung_sebagai_status_aktif_lcd(): void
    {
        $meja = $this->meja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $sesi->update(['dibuka_pada' => now()->subMinutes(5)]);
        $menu = $this->menu();

        $this->pesanan(
            $meja,
            $menu,
            'tunai',
            Pesanan::PAYMENT_SUDAH_BAYAR,
            Pesanan::STATUS_DIANTAR,
        );

        $this->pesanan(
            $meja,
            $menu,
            'tunai',
            Pesanan::PAYMENT_SUDAH_BAYAR,
            Pesanan::STATUS_SELESAI,
        );

        $data = $this->dataMeja($meja);

        $this->assertSame('lunas', $data['fase']);
        $this->assertTrue($data['ada_pesanan']);
        $this->assertTrue($data['semua_lunas']);
        $this->assertSame(50000.0, (float) $data['total_bayar']);
        $this->assertSame(1, $data['jumlah_pesanan']);
        $this->assertSame(1, $data['jumlah_selesai']);
        $this->assertCount(1, $data['pesanan']);
        $this->assertSame(Pesanan::STATUS_DIANTAR, $data['pesanan'][0]['status_pesanan']);
    }

    public function test_kasir_hanya_melihat_notifikasi_panggilan_dan_pelayan_yang_menyelesaikan(): void
    {
        $meja = $this->meja();
        $kasir = User::create([
            'nama' => 'Kasir Uji',
            'email' => 'kasir-' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'kasir',
            'is_aktif' => true,
        ]);
        $pelayan = User::create([
            'nama' => 'Pelayan Uji',
            'email' => 'pelayan-' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'pelayan',
            'area' => $meja->area,
            'is_aktif' => true,
        ]);

        $panggilan = LogPanggilPelayan::create([
            'meja_id' => $meja->id,
            'status_panggilan' => 'menunggu',
            'waktu_panggil' => now()->subMinute(),
        ]);

        $this->actingAs($kasir)
            ->get(route('kasir.dashboard'))
            ->assertOk()
            ->assertSee('PANGGILAN IOT')
            ->assertSee('PANGGILAN PELAYAN AKTIF')
            ->assertDontSee('Konfirmasi Panggilan');

        $this->actingAs($kasir)
            ->getJson(route('kasir.state'))
            ->assertOk()
            ->assertJsonPath('iotAlert.active', true)
            ->assertJsonPath('iotAlert.id', $panggilan->id);

        // Kasir tidak memiliki endpoint konfirmasi; hanya pelayan yang boleh.
        $this->actingAs($kasir)
            ->patchJson('/kasir/panggilan/' . $panggilan->id, [
                'status_panggilan' => 'selesai',
            ])
            ->assertNotFound();

        $this->assertSame('menunggu', $panggilan->fresh()->status_panggilan);

        $this->actingAs($pelayan)
            ->patchJson(route('pelayan.panggilan.ubah', $panggilan->id), [
                'status_panggilan' => 'selesai',
            ])
            ->assertOk()
            ->assertJsonPath('panggilan.status_panggilan', 'selesai');

        $panggilan->refresh();
        $this->assertSame('selesai', $panggilan->status_panggilan);
        $this->assertNotNull($panggilan->waktu_selesai);

        $this->actingAs($kasir)
            ->getJson(route('kasir.state'))
            ->assertOk()
            ->assertJsonPath('iotAlert.active', false)
            ->assertJsonPath('iotAlert.id', null);
    }
}
