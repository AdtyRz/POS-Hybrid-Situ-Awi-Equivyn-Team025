<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Models\User;
use App\Services\PesananService;
use App\Services\StokService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function pengguna(string $role): User
    {
        return User::create([
            'nama' => ucfirst($role).' Uji',
            'email' => $role.'-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => $role,
            'is_aktif' => true,
        ]);
    }

    private function meja(string $kode = 'LB-91'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-'.strtolower($kode).'-abcdefghij',
            'status_meja' => 'kosong',
        ]);
    }

    private function menu(int $stok = 10, int $harga = 10000): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan '.uniqid(),
            'target_kds' => 'dapur',
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Uji '.uniqid(),
            'slug' => 'nasi-uji-'.uniqid(),
            'harga' => $harga,
            'stok_menu' => $stok,
        ]);
    }

    public function test_halaman_admin_tidak_bisa_diakses_tanpa_login(): void
    {
        $this->get('/admin/master-menu')->assertRedirect(route('login'));
        $this->get('/admin/manajemen-akun')->assertRedirect(route('login'));
        $this->get('/admin/pengaturan-sistem')->assertRedirect(route('login'));
        $this->get('/admin/laporan-penjualan')->assertRedirect(route('login'));
    }

    public function test_kasir_tidak_bisa_membuka_halaman_admin(): void
    {
        $this->actingAs($this->pengguna('kasir'))
            ->get('/admin/manajemen-akun')
            ->assertForbidden();
    }

    public function test_admin_bisa_membuka_halaman_admin(): void
    {
        $admin = $this->pengguna('admin');

        $this->actingAs($admin)->get('/admin/master-menu')->assertOk();
        $this->actingAs($admin)->get('/admin/pengaturan-sistem')->assertOk();
    }

    public function test_callback_qris_palsu_sudah_tidak_tersedia(): void
    {
        $this->post('/qris/callback', [
            'order_id' => 'SW-APA-SAJA',
            'transaction_status' => 'settlement',
            'test_success' => true,
        ])->assertNotFound();
    }

    public function test_api_pesanan_memakai_kode_kanonik_tanpa_pajak_dan_stok_baru_dipotong_setelah_bayar(): void
    {
        $meja = $this->meja();
        $menu = $this->menu(10, 10000);

        $response = $this->postJson('/api/v1/pesanan', [
            'meja_id' => $meja->id,
            'qr_token' => $meja->qr_token,
            'jumlah' => [$menu->id => 2],
        ])->assertCreated();

        $data = $response->json('data');

        $this->assertStringStartsWith('SW-', $data['kode_pesanan']);
        $this->assertStringContainsString('-'.$meja->id, $data['kode_pesanan']);
        $this->assertSame(20000.0, (float) $data['total_bayar']);
        $this->assertSame(Pesanan::STATUS_MENUNGGU, $data['status_pesanan']);
        $this->assertSame(Pesanan::PAYMENT_BELUM_BAYAR, $data['status_pembayaran']);
        $this->assertSame(10, $menu->fresh()->stok_menu);
        $this->assertSame('terisi', $meja->fresh()->status_meja);
        $this->assertDatabaseMissing('stok_mutasi', [
            'menu_id' => $menu->id,
            'jenis' => 'penjualan',
        ]);
    }

    public function test_api_pesanan_tetap_bisa_dipesan_saat_meja_terisi(): void
    {
        $meja = $this->meja();
        $menu = $this->menu();
        $meja->update(['status_meja' => 'terisi']);

        $this->postJson('/api/v1/pesanan', [
            'meja_id' => $meja->id,
            'qr_token' => $meja->qr_token,
            'jumlah' => [$menu->id => 1],
        ])->assertCreated();
    }

    public function test_api_pesanan_menolak_stok_kurang_tanpa_mengubah_stok(): void
    {
        $meja = $this->meja();
        $menu = $this->menu(1);

        $this->postJson('/api/v1/pesanan', [
            'meja_id' => $meja->id,
            'qr_token' => $meja->qr_token,
            'jumlah' => [$menu->id => 2],
        ])->assertStatus(422)->assertJsonPath('success', false);

        $this->assertSame(1, $menu->fresh()->stok_menu);
    }

    public function test_pembayaran_kasir_tidak_mengosongkan_meja(): void
    {
        $kasir = $this->pengguna('kasir');
        $meja = $this->meja();
        $menu = $this->menu(10, 10000);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'kasir', 'metode' => 'tunai', 'kasir_id' => $kasir->id]
        );

        $this->actingAs($kasir)->post('/kasir/pembayaran', [
            'kode_pesanan' => $pesanan->kode_pesanan,
            'metode_pembayaran' => 'tunai',
            'jumlah_bayar' => 11000,
        ])->assertRedirect(route('kasir.dashboard', ['meja' => $meja->kode_meja]));

        $this->assertSame('terisi', $meja->fresh()->status_meja);
        $this->assertSame(Pesanan::PAYMENT_SUDAH_BAYAR, $pesanan->fresh()->status_pembayaran);
        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->fresh()->status_pesanan);
    }

    public function test_tutup_sesi_ditolak_bila_masih_ada_tagihan_belum_dibayar(): void
    {
        $meja = $this->meja();
        $menu = $this->menu(10);
        $service = app(PesananService::class);

        $lunas = $service->buat($meja, [$menu->id => 1], [], ['asal' => 'kasir']);
        $lunas->update(['status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR]);

        $belumBayar = $service->buat($meja, [$menu->id => 2], [], ['asal' => 'kasir']);

        $this->expectException(\RuntimeException::class);

        try {
            SesiMeja::putuskanSesi($meja);
        } finally {
            $this->assertSame(Pesanan::STATUS_MENUNGGU, $lunas->fresh()->status_pesanan);
            $this->assertSame(Pesanan::STATUS_MENUNGGU, $belumBayar->fresh()->status_pesanan);
            $this->assertSame(Pesanan::PAYMENT_SUDAH_BAYAR, $lunas->fresh()->status_pembayaran);
            $this->assertSame(Pesanan::PAYMENT_BELUM_BAYAR, $belumBayar->fresh()->status_pembayaran);
            $this->assertNull($belumBayar->fresh()->void_at);
            $this->assertSame('terisi', $meja->fresh()->status_meja);
            $this->assertSame(10, $menu->fresh()->stok_menu);
        }
    }

    public function test_pembatalan_lewat_kasir_mengembalikan_stok(): void
    {
        $kasir = $this->pengguna('kasir');
        $meja = $this->meja();
        $menu = $this->menu(10);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 2],
            [],
            ['asal' => 'kasir', 'metode' => 'tunai', 'kasir_id' => $kasir->id]
        );

        // Stok baru dipotong setelah pembayaran.
        app(StokService::class)->potongSetelahPembayaran($pesanan, $kasir->id);
        $pesanan->update(['status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR]);

        $this->assertSame(8, $menu->fresh()->stok_menu);

        $this->actingAs($kasir)->patch('/pesanan/'.$pesanan->kode_pesanan.'/status', [
            'status_pesanan' => 'dibatalkan',
            'alasan' => 'Salah input',
        ])->assertRedirect();

        $this->assertSame(10, $menu->fresh()->stok_menu);
        $this->assertSame(Pesanan::STATUS_DIBATALKAN, $pesanan->fresh()->status_pesanan);
        $this->assertSame('Salah input', $pesanan->fresh()->alasan_void);
        $this->assertDatabaseHas('stok_mutasi', [
            'menu_id' => $menu->id,
            'jenis' => 'pembatalan',
            'jumlah_delta' => 2,
        ]);
    }

    public function test_role_middleware_mengembalikan_json_bukan_redirect(): void
    {
        $this->postJson('/api/v1/pembayaran', [])->assertUnauthorized();

        $pelayan = $this->pengguna('pelayan');

        $this->actingAs($pelayan)
            ->postJson('/api/v1/pembayaran', [])
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }
}
