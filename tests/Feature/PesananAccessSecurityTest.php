<?php

namespace Tests\Feature;

use App\Events\PanggilanPelayan;
use App\Models\Kategori;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

class PesananAccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    private static int $urutan = 0;

    private function buatMeja(string $kode = 'LB-01'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'QR-' . $kode . '-' . Str::random(20),
            'status_meja' => 'kosong',
        ]);
    }

    private function buatMenu(string $target = 'dapur', ?string $nama = null): Menu
    {
        $nama ??= 'Menu Uji ' . Str::random(5);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori ' . Str::random(6),
            'target_kds' => $target,
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => $nama,
            'slug' => Str::slug($nama) . '-' . Str::lower(Str::random(4)),
            'harga' => 25000,
            'stok_menu' => 20,
            'is_aktif' => true,
        ]);
    }

    private function buatPesanan(MejaMakan $meja, Menu $menu, array $atribut = []): Pesanan
    {
        self::$urutan++;

        $pesanan = Pesanan::create(array_merge([
            'kode_pesanan' => 'SW-UJI-' . self::$urutan . '-' . Str::upper(Str::random(4)),
            'meja_id' => $meja->id,
            'total_bayar' => 25000,
            'status_pesanan' => Pesanan::STATUS_MENUNGGU,
            'status_pembayaran' => Pesanan::PAYMENT_BELUM_BAYAR,
        ], $atribut));

        $pesanan->detailPesanan()->create([
            'menu_id' => $menu->id,
            'jumlah' => 1,
            'harga_satuan' => 25000,
            'subtotal' => 25000,
            'status_item' => 'menunggu',
        ]);

        return $pesanan->fresh();
    }

    private function sesi(MejaMakan $meja): SesiMeja
    {
        return SesiMeja::aktifUntuk($meja);
    }

    public function test_halaman_detail_pesanan_hanya_bisa_dibuka_dengan_sesi_meja_yang_sama(): void
    {
        $meja = $this->buatMeja();
        $mejaLain = $this->buatMeja('LB-02');
        $menu = $this->buatMenu();
        $sesi = $this->sesi($meja);
        $sesiLain = $this->sesi($mejaLain);
        $pesanan = $this->buatPesanan($meja, $menu);

        $this->get(route('emenu.status', $pesanan->kode_pesanan))
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja));

        $this->withSession(['sesi_meja_' . $mejaLain->id => $sesiLain->id])
            ->get(route('emenu.status', $pesanan->kode_pesanan))
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja));

        $this->flushSession();

        foreach (['emenu.verifikasi', 'emenu.bayar-tunai'] as $namaRoute) {
            $this->get(route($namaRoute, $pesanan->kode_pesanan))
                ->assertRedirect(route('emenu.kunci', $meja->kode_meja));
        }

        $this->getJson(route('emenu.qris.status', $pesanan->kode_pesanan))
            ->assertForbidden()
            ->assertJsonPath('lunas', false);

        $this->withSession(['sesi_meja_' . $meja->id => $sesi->id])
            ->get(route('emenu.status', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee($pesanan->kode_pesanan);
    }

    public function test_pesanan_lama_tidak_bisa_dibuka_setelah_sesi_berganti(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();
        $sesiLama = $this->sesi($meja);

        $pesanan = $this->buatPesanan($meja, $menu, [
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'metode_pembayaran' => 'tunai',
            'waktu_pesan' => now()->subMinute(),
        ]);

        $sesiBaru = SesiMeja::putuskanSesi($meja);

        $this->withSession(['sesi_meja_' . $meja->id => $sesiLama->id])
            ->get(route('emenu.status', $pesanan->kode_pesanan))
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja));

        $this->withSession(['sesi_meja_' . $meja->id => $sesiBaru->id])
            ->get(route('emenu.status', $pesanan->kode_pesanan))
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja));

        $this->getJson('/api/v1/pesanan/' . $pesanan->kode_pesanan . '?qr_token=' . $meja->qr_token)
            ->assertForbidden();
    }

    public function test_panggil_pelayan_web_wajib_sesi_aktif(): void
    {
        $meja = $this->buatMeja();
        $sesi = $this->sesi($meja);

        $this->post(route('emenu.panggil', $meja->id))
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja));

        $this->assertSame(0, LogPanggilPelayan::count());

        $this->withSession(['sesi_meja_' . $meja->id => $sesi->id])
            ->post(route('emenu.panggil', $meja->id))
            ->assertRedirect();

        $this->assertSame(1, LogPanggilPelayan::count());
    }

    public function test_api_detail_pesanan_wajib_bukti_akses_dan_menyembunyikan_token_meja(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();
        $sesi = $this->sesi($meja);
        $pesanan = $this->buatPesanan($meja, $menu);

        $this->getJson('/api/v1/pesanan/' . $pesanan->kode_pesanan)
            ->assertStatus(401);

        $this->getJson('/api/v1/pesanan/' . $pesanan->kode_pesanan . '?qr_token=' . $meja->qr_token)
            ->assertOk()
            ->assertJsonPath('data.kode_pesanan', $pesanan->kode_pesanan)
            ->assertJsonMissingPath('data.meja.qr_token')
            ->assertJsonMissingPath('data.meja.id_device');

        $this->getJson('/api/v1/pesanan/' . $pesanan->kode_pesanan . '?kode_saung=' . $sesi->kode_unik)
            ->assertOk();

        $this->getJson('/api/v1/pesanan/' . $pesanan->kode_pesanan . '?kode_saung=0000')
            ->assertForbidden();
    }

    public function test_iot_wajib_token_dan_kode_sesi_aktif(): void
    {
        config()->set('services.iot.token', 'token-iot-uji');

        $meja = $this->buatMeja();
        $sesi = $this->sesi($meja);

        $this->getJson('/api/iot/status-meja')->assertStatus(401);

        $this->withHeader('X-IoT-Token', 'token-iot-uji')
            ->getJson('/api/iot/status-meja')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->withHeader('X-IoT-Token', 'token-iot-uji')
            ->getJson('/api/iot/meja/' . $meja->kode_meja)
            ->assertOk()
            ->assertJsonPath('data.kode_sesi', $sesi->kode_unik);

        $this->withHeader('X-IoT-Token', 'token-iot-uji')
            ->postJson('/api/iot/panggil-pelayan', ['kode_meja' => $meja->kode_meja])
            ->assertStatus(422);

        $this->withHeader('X-IoT-Token', 'token-iot-uji')
            ->postJson('/api/iot/panggil-pelayan', [
                'kode_meja' => $meja->kode_meja,
                'kode_sesi' => '9999',
            ])
            ->assertForbidden();

        Event::fake([PanggilanPelayan::class]);

        $this->withHeader('X-IoT-Token', 'token-iot-uji')
            ->postJson('/api/iot/panggil-pelayan', [
                'kode_meja' => $meja->kode_meja,
                'kode_sesi' => $sesi->kode_unik,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        Event::assertDispatched(PanggilanPelayan::class);
        $this->assertSame(1, LogPanggilPelayan::count());
    }
}
