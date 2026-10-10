<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CriticalFixesTest extends TestCase
{
    use RefreshDatabase;

    private function buatMeja(string $kode = 'LB-01'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'QR-' . $kode,
            'status_meja' => 'kosong',
        ]);
    }

    private function buatMenu(int $stok = 10): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan',
            'target_kds' => 'dapur',
        ]);

        $nama = 'Menu ' . Menu::count();

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => $nama,
            'slug' => Str::slug($nama),
            'harga' => 25000,
            'stok_menu' => $stok,
        ]);
    }

    private function buatPesanan(MejaMakan $meja, string $status = 'menunggu'): Pesanan
    {
        return Pesanan::create([
            'kode_pesanan' => 'AWI-TEST-' . Pesanan::count(),
            'meja_id' => $meja->id,
            'total_bayar' => 25000,
            'status_pesanan' => $status,
            'status_pembayaran' => 'belum_bayar',
        ]);
    }

    // A.1 — konflik route /pesanan/{...}
    public function test_detail_pesanan_staf_terlindungi_dan_tidak_tertimpa_e_menu(): void
    {
        $meja = $this->buatMeja();
        $pesanan = $this->buatPesanan($meja);

        $this->assertSame('/status/' . $pesanan->kode_pesanan, route('emenu.status', $pesanan->kode_pesanan, absolute: false));
        $this->assertSame('/pesanan/' . $pesanan->kode_pesanan, route('pesanan.show', $pesanan->kode_pesanan, absolute: false));

        $this->get('/pesanan/' . $pesanan->kode_pesanan)->assertRedirect('/login');
        $this->get('/status/' . $pesanan->kode_pesanan)->assertStatus(200);
    }

    public function test_pelayan_bisa_membuka_detail_pesanan_staf(): void
    {
        $meja = $this->buatMeja();
        $pesanan = $this->buatPesanan($meja);

        $pelayan = User::factory()->create(['role' => 'pelayan']);

        $this->actingAs($pelayan)
            ->get('/pesanan/' . $pesanan->kode_pesanan)
            ->assertStatus(200);
    }

    // A.2 — otorisasi API v1
    public function test_api_ubah_status_butuh_login(): void
    {
        $meja = $this->buatMeja();
        $pesanan = $this->buatPesanan($meja);

        $this->patchJson('/api/v1/pesanan/' . $pesanan->kode_pesanan . '/status', [
            'status_pesanan' => 'diproses',
        ])->assertStatus(401);

        $this->assertSame('menunggu', $pesanan->fresh()->status_pesanan);
    }

    public function test_api_ubah_status_oleh_kasir_berhasil(): void
    {
        $meja = $this->buatMeja();
        $pesanan = $this->buatPesanan($meja);
        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)
            ->patchJson('/api/v1/pesanan/' . $pesanan->kode_pesanan . '/status', [
                'status_pesanan' => 'diproses',
            ])
            ->assertStatus(200);

        $this->assertSame('diproses', $pesanan->fresh()->status_pesanan);
    }

    public function test_api_ubah_status_role_salah_ditolak(): void
    {
        $meja = $this->buatMeja();
        $pesanan = $this->buatPesanan($meja);
        $pelanggan = User::factory()->create(['role' => 'pelanggan']);

        $this->actingAs($pelanggan)
            ->patchJson('/api/v1/pesanan/' . $pesanan->kode_pesanan . '/status', [
                'status_pesanan' => 'diproses',
            ])
            ->assertStatus(403);
    }

    public function test_api_pembayaran_butuh_login_dan_role_kasir(): void
    {
        $meja = $this->buatMeja();
        $pesanan = $this->buatPesanan($meja);

        $body = [
            'kode_pesanan' => $pesanan->kode_pesanan,
            'metode_pembayaran' => 'tunai',
            'jumlah_bayar' => 50000,
        ];

        $this->postJson('/api/v1/pembayaran', $body)->assertStatus(401);

        $koki = User::factory()->create(['role' => 'koki']);
        $this->actingAs($koki)->postJson('/api/v1/pembayaran', $body)->assertStatus(403);

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->postJson('/api/v1/pembayaran', $body)->assertStatus(201);
    }

    public function test_api_antrean_panggilan_iot_butuh_pelayan(): void
    {
        $this->getJson('/api/v1/iot/panggilan')->assertStatus(401);

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->getJson('/api/v1/iot/panggilan')->assertStatus(403);

        $pelayan = User::factory()->create(['role' => 'pelayan']);
        $this->actingAs($pelayan)->getJson('/api/v1/iot/panggilan')->assertStatus(200);
    }

    // A.2 — pesanan tamu diamankan QR token
    public function test_api_buat_pesanan_tanpa_qr_token_ditolak(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $this->postJson('/api/v1/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menu->id => 1],
        ])->assertStatus(422)->assertJsonValidationErrors('qr_token');

        $this->assertSame(0, Pesanan::count());
    }

    public function test_api_buat_pesanan_qr_token_salah_ditolak(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $this->postJson('/api/v1/pesanan', [
            'meja_id' => $meja->id,
            'qr_token' => 'QR-SALAH',
            'jumlah' => [$menu->id => 1],
        ])->assertStatus(422);

        $this->assertSame(0, Pesanan::count());
    }

    public function test_api_buat_pesanan_qr_token_benar_berhasil(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $this->postJson('/api/v1/pesanan', [
            'meja_id' => $meja->id,
            'qr_token' => $meja->qr_token,
            'jumlah' => [$menu->id => 2],
        ])->assertStatus(201);

        $this->assertSame(1, Pesanan::count());
    }

    public function test_api_daftar_meja_tidak_membocorkan_qr_token(): void
    {
        $this->buatMeja();

        $this->getJson('/api/v1/meja')
            ->assertStatus(200)
            ->assertJsonMissingPath('data.0.qr_token');
    }

    // A.4 — login menolak akun nonaktif
    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        $user = User::factory()->create(['is_aktif' => false]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_akun_aktif_bisa_login(): void
    {
        $user = User::factory()->create(['is_aktif' => true]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }
}
