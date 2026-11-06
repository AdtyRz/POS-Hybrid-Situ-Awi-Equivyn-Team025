<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Models\User;
use App\Services\MidtransService;
use App\Services\PesananService;
use Database\Seeders\SituAwiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function pengguna(string $role = 'kasir', bool $aktif = true, array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role,
            'is_aktif' => $aktif,
        ], $atribut));
    }

    private function meja(?string $kode = null): MejaMakan
    {
        $kode ??= 'LB-'.Str::upper(Str::random(4));

        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'QR-'.Str::random(20),
            'status_meja' => 'kosong',
        ]);
    }

    private function menu(int $stok = 10, int $harga = 25000): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori '.Str::random(8),
            'target_kds' => 'dapur',
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Menu '.Str::random(8),
            'slug' => 'menu-'.Str::lower(Str::random(10)),
            'harga' => $harga,
            'stok_menu' => $stok,
            'is_aktif' => true,
        ]);
    }

    private function pesananQris(MejaMakan $meja, Menu $menu, int $jumlah = 1): Pesanan
    {
        return app(PesananService::class)->buat(
            $meja,
            [$menu->id => $jumlah],
            [],
            ['asal' => 'emenu', 'metode' => 'qris', 'nama_pelanggan' => 'Tamu Uji']
        );
    }

    private function statusMidtrans(Pesanan $pesanan, string $status, string $fraud = 'accept'): object
    {
        return (object) [
            'order_id' => $pesanan->kode_pesanan,
            'gross_amount' => (string) (float) $pesanan->total_bayar,
            'transaction_status' => $status,
            'fraud_status' => $fraud,
            'transaction_id' => 'trx-'.Str::lower(Str::random(8)),
        ];
    }

    private function sisipkanSesi(User $user): void
    {
        DB::table('sessions')->insert([
            'id' => 'sesi-uji-'.Str::random(24),
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ]);
    }

    public function test_redirect_bayar_tunai_tidak_membocorkan_kode_sesi(): void
    {
        $meja = $this->meja();
        $menu = $this->menu();
        $sesi = SesiMeja::aktifUntuk($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );
        $pesanan->update([
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
        ]);

        $response = $this->get(route('emenu.bayar-tunai', $pesanan->kode_pesanan));

        $response->assertRedirect(route('emenu.status', $pesanan->kode_pesanan));

        $location = (string) $response->headers->get('Location');
        $this->assertStringNotContainsString('kode_saung', $location);
        $this->assertStringNotContainsString($sesi->kode_unik, $location);

        $status = $this->getJson(route('emenu.bayar-tunai.status', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertJsonPath('lunas', true);

        $this->assertStringNotContainsString('kode_saung', (string) $status->json('redirect'));
        $this->assertStringNotContainsString($sesi->kode_unik, (string) $status->json('redirect'));
    }

    public function test_akun_nonaktif_tidak_bisa_membuka_halaman_admin_atau_cadangan(): void
    {
        $admin = $this->pengguna('admin', false);

        $this->actingAs($admin)
            ->get(route('admin.pengaturan-sistem.cadangan'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_penonaktifan_akun_mencabut_remember_token_dan_sesi(): void
    {
        $admin = $this->pengguna('admin');
        $target = $this->pengguna('kasir', true, ['remember_token' => 'token-remember-lama']);

        $this->sisipkanSesi($target);

        $this->actingAs($admin)
            ->patch(route('admin.akun.status', $target->id))
            ->assertRedirect();

        $target->refresh();

        $this->assertFalse($target->is_aktif);
        $this->assertNull($target->remember_token);
        $this->assertDatabaseMissing('sessions', ['user_id' => $target->id]);
    }

    public function test_admin_tidak_bisa_mengubah_status_akun_sendiri(): void
    {
        $admin = $this->pengguna('admin');

        $this->actingAs($admin)
            ->from(route('admin.akun'))
            ->patch(route('admin.akun.status', $admin->id))
            ->assertRedirect(route('admin.akun'))
            ->assertSessionHasErrors('akun');

        $this->assertTrue($admin->fresh()->is_aktif);
    }

    public function test_admin_nonaktif_tidak_bisa_mengaktifkan_dirinya_sendiri(): void
    {
        $admin = $this->pengguna('admin', false);

        $this->withoutMiddleware(\App\Http\Middleware\CheckIsActive::class);

        $this->actingAs($admin)
            ->from(route('admin.akun'))
            ->patch(route('admin.akun.status', $admin->id))
            ->assertRedirect(route('admin.akun'))
            ->assertSessionHasErrors('akun');

        $this->assertFalse($admin->fresh()->is_aktif);
    }

    public function test_perubahan_password_mencabut_sesi_dan_remember_token(): void
    {
        $admin = $this->pengguna('admin');
        $target = $this->pengguna('kasir', true, ['remember_token' => 'token-remember-lama']);

        $this->sisipkanSesi($target);

        $this->actingAs($admin)
            ->put(route('admin.akun.ubah', $target->id), [
                'nama' => $target->nama,
                'email' => $target->email,
                'role' => $target->role,
                'password' => 'SandiBaru123!',
            ])
            ->assertRedirect();

        $target->refresh();

        $this->assertTrue(Hash::check('SandiBaru123!', $target->password));
        $this->assertNull($target->remember_token);
        $this->assertDatabaseMissing('sessions', ['user_id' => $target->id]);
    }

    public function test_kasir_tidak_bisa_menandai_qris_lunas_tanpa_verifikasi_midtrans(): void
    {
        $kasir = $this->pengguna('kasir');
        $meja = $this->meja();
        $menu = $this->menu(10, 25000);
        $pesanan = $this->pesananQris($meja, $menu, 2);

        $this->assertSame(Pesanan::STATUS_MENUNGGU_VERIFIKASI, $pesanan->status_pesanan);
        $this->assertSame(Pesanan::PAYMENT_PENDING_VERIFIKASI, $pesanan->status_pembayaran);

        $this->partialMock(MidtransService::class, function ($mock) use ($pesanan) {
            $mock->shouldReceive('getTransactionStatus')
                ->once()
                ->andReturn($this->statusMidtrans($pesanan, 'pending'));
        });

        $this->actingAs($kasir)
            ->from(route('kasir.pembayaran.create', $pesanan->kode_pesanan))
            ->post(route('kasir.pembayaran.store'), [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'qris',
                'jumlah_bayar' => (float) $pesanan->total_bayar,
            ])
            ->assertRedirect(route('kasir.pembayaran.create', $pesanan->kode_pesanan))
            ->assertSessionHasErrors('metode_pembayaran');

        $pesanan->refresh();

        $this->assertSame(Pesanan::PAYMENT_PENDING_VERIFIKASI, $pesanan->status_pembayaran);
        $this->assertSame(Pesanan::STATUS_MENUNGGU_VERIFIKASI, $pesanan->status_pesanan);
        $this->assertDatabaseMissing('pembayaran', ['pesanan_id' => $pesanan->id]);
        $this->assertSame(10, (int) $menu->fresh()->stok_menu);
    }

    public function test_kasir_bisa_menandai_qris_lunas_setelah_settlement_midtrans(): void
    {
        $kasir = $this->pengguna('kasir');
        $meja = $this->meja();
        $menu = $this->menu(10, 25000);
        $pesanan = $this->pesananQris($meja, $menu, 2);

        $this->partialMock(MidtransService::class, function ($mock) use ($pesanan) {
            $mock->shouldReceive('getTransactionStatus')
                ->once()
                ->andReturn($this->statusMidtrans($pesanan, 'settlement'));
        });

        $this->actingAs($kasir)
            ->post(route('kasir.pembayaran.store'), [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'qris',
                'jumlah_bayar' => (float) $pesanan->total_bayar,
            ])
            ->assertRedirect(route('kasir.dashboard', ['meja' => $meja->kode_meja]));

        $pesanan->refresh();

        $this->assertSame(Pesanan::PAYMENT_SUDAH_BAYAR, $pesanan->status_pembayaran);
        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->status_pesanan);
        $this->assertDatabaseHas('pembayaran', [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'qris',
            'status_pembayaran' => 'lunas',
        ]);
        $this->assertSame(8, (int) $menu->fresh()->stok_menu);
    }

    public function test_api_pembayaran_qris_menolak_status_yang_belum_settlement(): void
    {
        $kasir = $this->pengguna('kasir');
        $meja = $this->meja();
        $menu = $this->menu(10, 25000);
        $pesanan = $this->pesananQris($meja, $menu);

        $this->partialMock(MidtransService::class, function ($mock) use ($pesanan) {
            $mock->shouldReceive('getTransactionStatus')
                ->once()
                ->andReturn($this->statusMidtrans($pesanan, 'pending'));
        });

        $this->actingAs($kasir)
            ->postJson('/api/v1/pembayaran', [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'qris',
                'jumlah_bayar' => (float) $pesanan->total_bayar,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $pesanan->refresh();

        $this->assertSame(Pesanan::PAYMENT_PENDING_VERIFIKASI, $pesanan->status_pembayaran);
        $this->assertDatabaseMissing('pembayaran', ['pesanan_id' => $pesanan->id]);
        $this->assertSame(10, (int) $menu->fresh()->stok_menu);
    }

    public function test_cadangan_tidak_mengekspor_password_remember_token_atau_kode_sesi(): void
    {
        $admin = $this->pengguna('admin', true, ['remember_token' => 'token-remember-rahasia']);
        $meja = $this->meja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $response = $this->actingAs($admin)
            ->get(route('admin.pengaturan-sistem.cadangan'))
            ->assertOk();

        $isi = $response->streamedContent();
        $data = json_decode($isi, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('users', $data['tabel']);
        $this->assertNotEmpty($data['tabel']['users']);
        $this->assertArrayNotHasKey('password', $data['tabel']['users'][0]);
        $this->assertArrayNotHasKey('remember_token', $data['tabel']['users'][0]);

        $sesiCadangan = collect($data['tabel']['sesi_meja'])->firstWhere('id', $sesi->id);
        $this->assertNotNull($sesiCadangan);
        $this->assertArrayNotHasKey('kode_unik', $sesiCadangan);

        $this->assertStringNotContainsString($admin->password, $isi);
        $this->assertStringNotContainsString('token-remember-rahasia', $isi);
    }

    public function test_pembayaran_kedua_ditolak_dan_stok_tidak_menjadi_negatif(): void
    {
        $kasir = $this->pengguna('kasir');
        $meja = $this->meja();
        $menu = $this->menu(5, 10000);
        $service = app(PesananService::class);

        $pertama = $service->buat(
            $meja,
            [$menu->id => 4],
            [],
            ['asal' => 'kasir', 'metode' => 'tunai', 'kasir_id' => $kasir->id]
        );
        $kedua = $service->buat(
            $meja,
            [$menu->id => 4],
            [],
            ['asal' => 'kasir', 'metode' => 'tunai', 'kasir_id' => $kasir->id]
        );

        $this->actingAs($kasir)
            ->post(route('kasir.pembayaran.store'), [
                'kode_pesanan' => $pertama->kode_pesanan,
                'metode_pembayaran' => 'tunai',
                'jumlah_bayar' => (float) $pertama->total_bayar,
            ])
            ->assertRedirect(route('kasir.dashboard', ['meja' => $meja->kode_meja]));

        $this->assertSame(1, (int) $menu->fresh()->stok_menu);
        $this->assertSame(Pesanan::PAYMENT_SUDAH_BAYAR, $pertama->fresh()->status_pembayaran);

        $this->actingAs($kasir)
            ->from(route('kasir.pembayaran.create', $kedua->kode_pesanan))
            ->post(route('kasir.pembayaran.store'), [
                'kode_pesanan' => $kedua->kode_pesanan,
                'metode_pembayaran' => 'tunai',
                'jumlah_bayar' => (float) $kedua->total_bayar,
            ])
            ->assertRedirect(route('kasir.pembayaran.create', $kedua->kode_pesanan))
            ->assertSessionHasErrors('kode_pesanan');

        $kedua->refresh();

        $this->assertSame(1, (int) $menu->fresh()->stok_menu);
        $this->assertSame(Pesanan::PAYMENT_BELUM_BAYAR, $kedua->status_pembayaran);
        $this->assertSame(Pesanan::STATUS_MENUNGGU, $kedua->status_pesanan);
        $this->assertDatabaseMissing('pembayaran', ['pesanan_id' => $kedua->id]);
    }

    public function test_seeder_menolak_password_default_di_luar_local_dan_testing(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SEED_STAFF_PASSWORD');

        (new SituAwiSeeder())->run();
    }

    public function test_example_env_mematikan_debug(): void
    {
        $isi = file_get_contents(base_path('.env.example'));

        $this->assertMatchesRegularExpression('/^APP_DEBUG=false$/m', $isi);
    }
}
