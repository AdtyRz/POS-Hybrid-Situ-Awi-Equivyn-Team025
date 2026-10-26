<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_api_iot_panggilan_role_salah_ditolak(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)
            ->getJson('/api/v1/iot/panggilan')
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

    public function test_halaman_master_menu_menampilkan_data_database(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => 'admin']);
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan',
            'target_kds' => 'dapur',
        ]);
        Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Menu Tampil Database',
            'slug' => 'menu-tampil-database',
            'harga' => 18000,
            'stok_menu' => 6,
        ]);

        $this->actingAs($admin)->get(route('admin.master-menu'))
            ->assertOk()
            ->assertSee('Master Data Menu & Stok')
            ->assertSee('Menu Tampil Database')
            ->assertDontSee('Muat Ulang');

        $this->actingAs($admin)->getJson(route('admin.menu.data'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Menu Tampil Database'])
            ->assertJsonFragment(['photo' => '']);
    }

    public function test_harga_menu_melebihi_batas_kolom_ditolak(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan',
            'target_kds' => 'dapur',
        ]);

        $this->actingAs($admin)->post(route('admin.menu.simpan'), [
            'nama_menu' => 'Menu Harga Overflow',
            'kategori_id' => $kategori->id,
            'harga' => 9999999999999,
            'stok_menu' => 1,
            'foto_menu' => UploadedFile::fake()->create('menu.jpg', 100, 'image/jpeg'),
        ])->assertSessionHasErrors('harga');

        $this->assertDatabaseMissing('menu', ['nama_menu' => 'Menu Harga Overflow']);
        $this->assertSame([], Storage::disk('public')->files('menu'));
    }

    public function test_admin_dapat_mengunggah_foto_menu(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan',
            'target_kds' => 'dapur',
        ]);

        $this->actingAs($admin)->post(route('admin.menu.simpan'), [
            'nama_menu' => 'Menu Foto Uji',
            'kategori_id' => $kategori->id,
            'harga' => 15000,
            'stok_menu' => 5,
            'deskripsi' => 'Menu dengan foto',
            'foto_menu' => UploadedFile::fake()->create('menu.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();

        $menu = Menu::where('nama_menu', 'Menu Foto Uji')->firstOrFail();

        $this->assertNotNull($menu->foto_menu);
        Storage::disk('public')->assertExists($menu->foto_menu);
    }

    public function test_admin_dapat_mengganti_foto_menu_dan_foto_lama_dihapus(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('menu/foto-lama.jpg', 'lama');

        $admin = User::factory()->create(['role' => 'admin']);
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan',
            'target_kds' => 'dapur',
        ]);
        $menu = Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Menu Ganti Foto',
            'slug' => 'menu-ganti-foto',
            'harga' => 15000,
            'stok_menu' => 5,
            'foto_menu' => 'menu/foto-lama.jpg',
        ]);

        $this->actingAs($admin)->put(route('admin.menu.ubah', $menu->id), [
            'nama_menu' => 'Menu Ganti Foto',
            'kategori_id' => $kategori->id,
            'harga' => 15000,
            'stok_menu' => 5,
            'foto_menu' => UploadedFile::fake()->create('foto-baru.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();

        $menu->refresh();

        Storage::disk('public')->assertMissing('menu/foto-lama.jpg');
        Storage::disk('public')->assertExists($menu->foto_menu);
        $this->assertNotSame('menu/foto-lama.jpg', $menu->foto_menu);
    }

    public function test_upload_foto_menu_menolak_ekstensi_php(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan',
            'target_kds' => 'dapur',
        ]);

        $this->actingAs($admin)->post(route('admin.menu.simpan'), [
            'nama_menu' => 'Menu Ekstensi PHP',
            'kategori_id' => $kategori->id,
            'harga' => 15000,
            'stok_menu' => 5,
            'foto_menu' => UploadedFile::fake()->create('shell.php', 100, 'image/jpeg'),
        ])->assertSessionHasErrors('foto_menu');

        $this->assertDatabaseMissing('menu', ['nama_menu' => 'Menu Ekstensi PHP']);
        $this->assertSame([], Storage::disk('public')->files('menu'));
    }

    public function test_stok_dipotong_setelah_pembayaran_dan_tidak_dua_kali(): void
    {
        $meja = $this->buatMeja('LB-21');
        $menu = $this->buatMenu(10);
        $pesanan = $this->buatPesanan($meja);
        $pesanan->update(['total_bayar' => 50000]);
        $pesanan->detailPesanan()->create([
            'menu_id' => $menu->id,
            'jumlah' => 2,
            'harga_satuan' => 25000,
            'subtotal' => 50000,
        ]);

        $kasir = User::factory()->create(['role' => 'kasir']);
        $body = [
            'kode_pesanan' => $pesanan->kode_pesanan,
            'metode_pembayaran' => 'tunai',
            'jumlah_bayar' => 50000,
        ];

        $this->actingAs($kasir)->postJson('/api/v1/pembayaran', $body)->assertStatus(201);

        $this->assertSame(8, $menu->fresh()->stok_menu);
        $this->assertDatabaseHas('stok_mutasi', [
            'menu_id' => $menu->id,
            'pesanan_id' => $pesanan->id,
            'jenis' => 'penjualan',
            'jumlah_delta' => -2,
            'stok_akhir' => 8,
        ]);

        $this->actingAs($kasir)->postJson('/api/v1/pembayaran', $body)->assertStatus(409);
        $this->assertSame(8, $menu->fresh()->stok_menu);
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
        $this->assertSame(10, $menu->fresh()->stok_menu);
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
