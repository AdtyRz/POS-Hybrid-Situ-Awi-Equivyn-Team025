<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PesananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class KasirMultiOrderLockTest extends TestCase
{
    use RefreshDatabase;

    private function kasir(): User
    {
        return User::create([
            'nama' => 'Kasir Multi Order',
            'email' => 'kasir-multi-' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'kasir',
            'is_aktif' => true,
        ]);
    }

    private function admin(): User
    {
        return User::create([
            'nama' => 'Admin Multi Order',
            'email' => 'admin-multi-' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'admin',
            'is_aktif' => true,
        ]);
    }

    private function meja(string $kode = 'LB-71'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-' . strtolower($kode) . '-abcdefghij',
            'status_meja' => 'kosong',
        ]);
    }

    private function menu(int $harga = 10000): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan ' . uniqid(),
            'target_kds' => 'dapur',
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Menu Multi ' . uniqid(),
            'slug' => 'menu-multi-' . uniqid(),
            'harga' => $harga,
            'stok_menu' => 50,
            'is_aktif' => true,
        ]);
    }

    private function pesanan(MejaMakan $meja, Menu $menu, int $jumlah = 1, array $opsi = []): Pesanan
    {
        return app(PesananService::class)->buat(
            $meja,
            [$menu->id => $jumlah],
            [],
            array_merge(['asal' => 'kasir', 'metode' => 'tunai'], $opsi),
        );
    }

    private function lunasi(Pesanan $pesanan): void
    {
        $pesanan->update([
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'metode_pembayaran' => 'tunai',
        ]);
    }

    public function test_satu_meja_memuat_semua_pesanan_aktif_di_dashboard_dan_state(): void
    {
        $this->withoutVite();

        $kasir = $this->kasir();
        $meja = $this->meja();
        $menu = $this->menu();

        $pertama = $this->pesanan($meja, $menu);
        $pertama->update(['waktu_pesan' => now()->subMinutes(5)]);

        $kedua = $this->pesanan($meja, $menu, 2);

        $response = $this->actingAs($kasir)->get(route('kasir.dashboard'));

        $response->assertOk();

        $saung = collect($response->viewData('saungsJson'))
            ->firstWhere('id', $meja->kode_meja);

        $this->assertSame(2, $saung['orderCount']);
        $this->assertSame(2, $saung['unpaidOrderCount']);
        $this->assertFalse($saung['allOrdersPaid']);
        $this->assertSame(
            [$pertama->kode_pesanan, $kedua->kode_pesanan],
            $saung['orders']->pluck('kodePesanan')->all(),
        );

        $state = $this->actingAs($kasir)
            ->getJson(route('kasir.state'))
            ->assertOk()
            ->json('saungs');

        $saungState = collect($state)->firstWhere('id', $meja->kode_meja);

        $this->assertSame(2, $saungState['orderCount']);
        $this->assertSame(
            [$pertama->kode_pesanan, $kedua->kode_pesanan],
            collect($saungState['orders'])->pluck('kodePesanan')->all(),
        );
    }

    public function test_status_meja_tetap_belum_lunas_bila_ada_order_dapur_yang_belum_dibayar(): void
    {
        $this->withoutVite();

        $kasir = $this->kasir();
        $meja = $this->meja('LB-72');
        $menu = $this->menu();

        $lunas = $this->pesanan($meja, $menu);
        $lunas->update(['waktu_pesan' => now()->subMinutes(5)]);
        $this->lunasi($lunas);

        $belumLunas = $this->pesanan($meja, $menu, 2);
        $belumLunas->update([
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_BELUM_BAYAR,
        ]);

        $response = $this->actingAs($kasir)->get(route('kasir.dashboard'));

        $response->assertOk();

        $saung = collect($response->viewData('saungsJson'))
            ->firstWhere('id', $meja->kode_meja);

        $this->assertSame(2, $saung['orderCount']);
        $this->assertSame(1, $saung['unpaidOrderCount']);
        $this->assertFalse($saung['allOrdersPaid']);
        $this->assertSame('preparing', $saung['status']);
    }

    public function test_lock_menyembunyikan_detail_dan_hanya_bisa_dibuka_dengan_password_akun_sendiri(): void
    {
        $this->withoutVite();

        $kasir = $this->kasir();
        $meja = $this->meja();
        $menu = $this->menu();
        $pesanan = $this->pesanan($meja, $menu);

        $this->actingAs($kasir)
            ->post(route('kasir.lock'))
            ->assertRedirect(route('kasir.locked'));

        $this->assertTrue(session('kasir_locked'));
        $this->assertSame('standby', $kasir->fresh()->status_operasional);

        $this->actingAs($kasir)
            ->get(route('kasir.dashboard'))
            ->assertRedirect(route('kasir.locked'));

        $this->actingAs($kasir)
            ->get(route('kasir.locked'))
            ->assertOk()
            ->assertSee('Terminal Terkunci')
            ->assertSee('Pesanan Baru')
            ->assertDontSee($pesanan->kode_pesanan)
            ->assertDontSee($menu->nama_menu);

        $this->actingAs($kasir)
            ->getJson(route('kasir.locked.notifications'))
            ->assertOk()
            ->assertJson([
                'pesanan_baru' => 1,
                'belum_bayar' => 1,
                'panggilan' => 0,
            ]);

        $this->actingAs($kasir)
            ->post(route('kasir.unlock'), ['password' => 'password-salah'])
            ->assertSessionHasErrors('password');

        $this->assertSame('standby', $kasir->fresh()->status_operasional);
        $this->assertTrue(session('kasir_locked'));

        $this->actingAs($kasir)
            ->post(route('kasir.unlock'), ['password' => 'password'])
            ->assertRedirect(route('kasir.dashboard'));

        $this->assertSame('aktif', $kasir->fresh()->status_operasional);
        $this->assertFalse(session()->has('kasir_locked'));

        $this->actingAs($kasir)
            ->get(route('kasir.dashboard'))
            ->assertOk();
    }

    public function test_status_standby_tampil_di_manajemen_akun(): void
    {
        $this->withoutVite();

        $admin = $this->admin();
        $kasir = $this->kasir();
        $kasir->update(['status_operasional' => 'standby']);

        $this->actingAs($admin)
            ->get(route('admin.manajemen-akun'))
            ->assertOk()
            ->assertSee('Standby');
    }

    public function test_login_dan_logout_mengembalikan_status_operasional_aktif(): void
    {
        $kasir = $this->kasir();
        $kasir->update(['status_operasional' => 'standby']);

        $this->post(route('login'), [
            'email' => $kasir->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame('aktif', $kasir->fresh()->status_operasional);

        $kasir->update(['status_operasional' => 'standby']);

        $this->post(route('logout'))->assertRedirect('/');

        $this->assertSame('aktif', $kasir->fresh()->status_operasional);
    }

    public function test_pembayaran_kasir_mengisi_kasir_id(): void
    {
        $kasir = $this->kasir();
        $meja = $this->meja();
        $menu = $this->menu();

        $pesanan = $this->pesanan($meja, $menu, 1, [
            'asal' => 'emenu',
            'metode' => 'tunai',
        ]);

        $this->assertNull($pesanan->kasir_id);

        $this->actingAs($kasir)->post(route('kasir.pembayaran.store'), [
            'kode_pesanan' => $pesanan->kode_pesanan,
            'metode_pembayaran' => 'tunai',
            'jumlah_bayar' => (float) $pesanan->total_bayar,
        ])->assertRedirect(route('kasir.dashboard', ['meja' => $meja->kode_meja]));

        $pesanan->refresh();

        $this->assertSame($kasir->id, $pesanan->kasir_id);
        $this->assertSame(Pesanan::PAYMENT_SUDAH_BAYAR, $pesanan->status_pembayaran);
        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->status_pesanan);
    }

    public function test_tandai_struk_hanya_memilih_order_yang_dicentang_dan_mengisi_kasir(): void
    {
        $kasir = $this->kasir();
        $meja = $this->meja();
        $menu = $this->menu();

        $dipilih = $this->pesanan($meja, $menu);
        $tidakDipilih = $this->pesanan($meja, $menu, 2);
        $belumBayar = $this->pesanan($meja, $menu, 3);

        $this->lunasi($dipilih);
        $this->lunasi($tidakDipilih);

        $this->assertNull($dipilih->fresh()->kasir_id);
        $this->assertNull($tidakDipilih->fresh()->kasir_id);
        $this->assertNull($belumBayar->fresh()->kasir_id);

        $this->actingAs($kasir)
            ->postJson(route('kasir.struk.tandai'), [
                'meja_id' => $meja->id,
                'kode_pesanan' => [$dipilih->kode_pesanan, $belumBayar->kode_pesanan],
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'jumlah' => 1]);

        $this->assertNotNull($dipilih->fresh()->printed_at);
        $this->assertNull($tidakDipilih->fresh()->printed_at);
        $this->assertNull($belumBayar->fresh()->printed_at);
        $this->assertSame($kasir->id, $dipilih->fresh()->kasir_id);
        $this->assertNull($tidakDipilih->fresh()->kasir_id);
        $this->assertNull($belumBayar->fresh()->kasir_id);
    }

    public function test_pembatalan_pesanan_lunas_via_json_ditolak(): void
    {
        $kasir = $this->kasir();
        $meja = $this->meja();
        $menu = $this->menu();
        $pesanan = $this->pesanan($meja, $menu);

        $this->lunasi($pesanan);

        $this->actingAs($kasir)
            ->patchJson(route('pesanan.status', $pesanan->kode_pesanan), [
                'status_pesanan' => 'dibatalkan',
                'alasan' => 'Salah input',
            ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Pesanan yang sudah lunas tidak dapat dibatalkan.',
            ]);

        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->fresh()->status_pesanan);
        $this->assertNull($pesanan->fresh()->void_at);
    }

    public function test_endpoint_versi_realtime_butuh_login_dan_berubah_setelah_data_berubah(): void
    {
        $this->get(route('realtime.version'))->assertRedirect(route('login'));

        $kasir = $this->kasir();
        $meja = $this->meja();
        $menu = $this->menu();

        Cache::forget('realtime-version');
        $awal = $this->actingAs($kasir)
            ->getJson(route('realtime.version'))
            ->assertOk()
            ->json('version');

        Cache::forget('realtime-version');
        $this->pesanan($meja, $menu);

        $akhir = $this->actingAs($kasir)
            ->getJson(route('realtime.version'))
            ->assertOk()
            ->json('version');

        $this->assertNotSame($awal, $akhir);
    }
}
