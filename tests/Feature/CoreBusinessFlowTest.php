<?php

namespace Tests\Feature;

use App\Models\DetailPesanan;
use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\StokMutasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CoreBusinessFlowTest extends TestCase
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

    private function buatMenu(int $stok = 10, string $targetKds = 'dapur'): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori ' . $targetKds,
            'target_kds' => $targetKds,
        ]);

        $nama = 'Menu ' . $targetKds . ' ' . Menu::count();

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => $nama,
            'slug' => Str::slug($nama),
            'harga' => 25000,
            'stok_menu' => $stok,
        ]);
    }

    public function test_kode_pesanan_memakai_pola_awi(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)
            ->post('/kasir/pesanan', [
                'meja_id' => $meja->id,
                'jumlah' => [$menu->id => 2],
            ])
            ->assertRedirect();

        $pesanan = Pesanan::firstOrFail();

        $this->assertMatchesRegularExpression('/^AWI-\d{14}-\d{3}$/', $pesanan->kode_pesanan);
    }

    public function test_pesanan_baru_statusnya_menunggu_dan_belum_bayar(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menu->id => 2],
        ]);

        $pesanan = Pesanan::firstOrFail();

        $this->assertSame('menunggu', $pesanan->status_pesanan);
        $this->assertSame('belum_bayar', $pesanan->status_pembayaran);
        $this->assertSame(50000.0, (float) $pesanan->total_bayar);
        $this->assertSame(1, $pesanan->detailPesanan()->count());
    }

    public function test_memesan_mengurangi_stok_dan_mencatat_stok_mutasi(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu(10);

        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menu->id => 3],
        ]);

        $this->assertSame(7, (int) Menu::findOrFail($menu->id)->stok_menu);

        $mutasi = StokMutasi::firstOrFail();
        $this->assertSame('penjualan', $mutasi->jenis);
        $this->assertSame(-3, $mutasi->jumlah_delta);
        $this->assertSame(7, $mutasi->stok_akhir);
    }

    public function test_pesanan_menandai_meja_terisi(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menu->id => 1],
        ]);

        $this->assertSame('terisi', $meja->fresh()->status_meja);
    }

    public function test_pesanan_ditolak_kalau_stok_kurang(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu(2);

        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)
            ->post('/kasir/pesanan', [
                'meja_id' => $meja->id,
                'jumlah' => [$menu->id => 5],
            ])
            ->assertSessionHasErrors('jumlah');

        $this->assertSame(0, Pesanan::count());
        $this->assertSame(2, (int) Menu::findOrFail($menu->id)->stok_menu);
    }

    public function test_meja_yang_masih_terisi_ditolak(): void
    {
        $meja = $this->buatMeja();
        $meja->update(['status_meja' => 'terisi']);
        $menu = $this->buatMenu();

        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)
            ->post('/kasir/pesanan', [
                'meja_id' => $meja->id,
                'jumlah' => [$menu->id => 1],
            ])
            ->assertSessionHasErrors('meja_id');

        $this->assertSame(0, Pesanan::count());
    }

    public function test_kds_menampilkan_item_dapur_kategori_dapur(): void
    {
        $meja = $this->buatMeja();
        $menuDapur = $this->buatMenu(10, 'dapur');
        $menuBar = $this->buatMenu(10, 'bar');

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menuDapur->id => 1, $menuBar->id => 1],
        ]);

        $koki = User::factory()->create(['role' => 'koki']);

        $dapur = $this->actingAs($koki)->getJson('/kds/dapur')->assertStatus(200);
        $bar = $this->actingAs($koki)->getJson('/kds/bar')->assertStatus(403);

        $this->assertCount(1, $dapur->json('data'));
        $this->assertSame($menuDapur->id, $dapur->json('data.0.menu_id'));
    }

    public function test_koki_ubah_status_item_ke_siap(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu(10, 'dapur');

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menu->id => 1],
        ]);

        $detail = DetailPesanan::firstOrFail();
        $koki = User::factory()->create(['role' => 'koki']);

        $this->actingAs($koki)
            ->patchJson('/kds/dapur/' . $detail->id, ['status_item' => 'siap'])
            ->assertStatus(200);

        $this->assertSame('siap', $detail->fresh()->status_item);
        $this->assertSame('siap', Pesanan::firstOrFail()->status_pesanan);
    }

    public function test_koki_tidak_boleh_ubah_status_item_ke_status_asing(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu(10, 'dapur');

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menu->id => 1],
        ]);

        $detail = DetailPesanan::firstOrFail();
        $koki = User::factory()->create(['role' => 'koki']);

        $this->actingAs($koki)
            ->patchJson('/kds/dapur/' . $detail->id, ['status_item' => 'selesai'])
            ->assertStatus(422);

        $this->assertSame('menunggu', $detail->fresh()->status_item);
    }

    public function test_pembayaran_lunas_menutup_pesanan_dan_melepas_meja(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menu->id => 2],
        ]);

        $pesanan = Pesanan::firstOrFail();

        $this->actingAs($kasir)
            ->post('/kasir/pembayaran', [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'tunai',
                'jumlah_bayar' => 60000,
            ])
            ->assertRedirect();

        $pesanan->refresh();

        $this->assertSame('selesai', $pesanan->status_pesanan);
        $this->assertSame('sudah_bayar', $pesanan->status_pembayaran);
        $this->assertSame('tunai', $pesanan->metode_pembayaran);
        $this->assertSame('kosong', $meja->fresh()->status_meja);

        $bayar = Pembayaran::firstOrFail();
        $this->assertSame('lunas', $bayar->status_pembayaran);
        $this->assertSame(60000.0, (float) $bayar->jumlah_bayar);
        $this->assertSame(10000.0, (float) $bayar->kembalian);
    }

    public function test_pembayaran_ditolak_kalau_uangnya_kurang(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menu->id => 2],
        ]);

        $pesanan = Pesanan::firstOrFail();

        $this->actingAs($kasir)
            ->post('/kasir/pembayaran', [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'tunai',
                'jumlah_bayar' => 10000,
            ])
            ->assertSessionHasErrors('jumlah_bayar');

        $this->assertSame(0, Pembayaran::count());
        $this->assertSame('terisi', $meja->fresh()->status_meja);
    }

    public function test_pesanan_yang_sudah_lunas_tidak_bisa_dibayar_lagi(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menu->id => 1],
        ]);

        $pesanan = Pesanan::firstOrFail();

        $dataBayar = [
            'kode_pesanan' => $pesanan->kode_pesanan,
            'metode_pembayaran' => 'qris',
            'jumlah_bayar' => 25000,
        ];

        $this->actingAs($kasir)->post('/kasir/pembayaran', $dataBayar)->assertRedirect();
        $this->actingAs($kasir)->post('/kasir/pembayaran', $dataBayar)->assertSessionHasErrors('jumlah_bayar');

        $this->assertSame(1, Pembayaran::count());
    }

    public function test_halaman_pembayaran_menolak_pesanan_lunas(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menu->id => 1],
        ]);

        $pesanan = Pesanan::firstOrFail();

        $this->actingAs($kasir)->post('/kasir/pembayaran', [
            'kode_pesanan' => $pesanan->kode_pesanan,
            'metode_pembayaran' => 'qris',
            'jumlah_bayar' => 25000,
        ])->assertRedirect();

        $this->actingAs($kasir)
            ->get('/kasir/pembayaran/' . $pesanan->kode_pesanan)
            ->assertStatus(409);
    }

    public function test_pelanggan_tidak_bisa_membuat_pesanan_lewat_kasir(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $pelanggan = User::factory()->create(['role' => 'pelanggan']);

        $this->actingAs($pelanggan)
            ->post('/kasir/pesanan', [
                'meja_id' => $meja->id,
                'jumlah' => [$menu->id => 1],
            ])
            ->assertStatus(403);

        $this->assertSame(0, Pesanan::count());
    }

    public function test_koki_tidak_bisa_membuka_halaman_kasir(): void
    {
        $koki = User::factory()->create(['role' => 'koki']);

        $this->actingAs($koki)->get('/kasir/pesanan/tambah')->assertStatus(403);
    }

    public function test_laporan_admin_menampilkan_pendapatan(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu();

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menu->id => 4],
        ]);

        $pesanan = Pesanan::firstOrFail();
        $this->actingAs($kasir)->post('/kasir/pembayaran', [
            'kode_pesanan' => $pesanan->kode_pesanan,
            'metode_pembayaran' => 'tunai',
            'jumlah_bayar' => 100000,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $laporan = $this->actingAs($admin)->getJson('/admin/laporan')->assertStatus(200);

        $this->assertSame(1, $laporan->json('data.jumlah_pesanan'));
        $this->assertSame(1, $laporan->json('data.jumlah_pesanan_lunas'));
        $this->assertEquals(100000.0, (float) $laporan->json('data.total_pendapatan'));
        $this->assertSame(0, $laporan->json('data.jumlah_meja_terisi'));
    }

    public function test_pelanggan_bisa_pesan_lewat_e_menu_tanpa_login(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu(10, 'dapur');

        $this->post('/meja/' . $meja->id . '/checkout', [
            'jumlah' => [$menu->id => 2],
            'metode' => 'tunai',
        ])->assertRedirect();

        $pesanan = Pesanan::firstOrFail();

        $this->assertMatchesRegularExpression('/^AWI-\d{14}-\d{3}$/', $pesanan->kode_pesanan);
        $this->assertNull($pesanan->pelanggan_id);
        $this->assertSame('belum_bayar', $pesanan->status_pembayaran);
        $this->assertSame('terisi', $meja->fresh()->status_meja);
        $this->assertSame(8, (int) Menu::findOrFail($menu->id)->stok_menu);
    }

    public function test_e_menu_menolak_pesanan_kalau_stok_kurang(): void
    {
        $meja = $this->buatMeja();
        $menu = $this->buatMenu(1, 'dapur');

        $this->post('/meja/' . $meja->id . '/checkout', [
            'jumlah' => [$menu->id => 4],
            'metode' => 'tunai',
        ])->assertSessionHasErrors('jumlah');

        $this->assertSame(0, Pesanan::count());
        $this->assertSame('kosong', $meja->fresh()->status_meja);
    }

    public function test_alur_penuh_dari_pesan_sampai_meja_kosong(): void
    {
        $meja = $this->buatMeja('LA-01');
        $menuDapur = $this->buatMenu(10, 'dapur');
        $menuBar = $this->buatMenu(10, 'bar');

        $kasir = User::factory()->create(['role' => 'kasir']);
        $koki = User::factory()->create(['role' => 'koki']);
        $barista = User::factory()->create(['role' => 'barista']);

        $this->actingAs($kasir)->post('/kasir/pesanan', [
            'meja_id' => $meja->id,
            'jumlah' => [$menuDapur->id => 2, $menuBar->id => 1],
        ])->assertRedirect();

        $pesanan = Pesanan::firstOrFail();
        $this->assertSame('menunggu', $pesanan->status_pesanan);
        $this->assertSame('terisi', $meja->fresh()->status_meja);

        $this->actingAs($koki)->patchJson('/kds/dapur/' . DetailPesanan::firstOrFail()->id, [
            'status_item' => 'siap',
        ])->assertStatus(200);

        $this->actingAs($barista)->patchJson('/kds/bar/' . DetailPesanan::skip(1)->firstOrFail()->id, [
            'status_item' => 'siap',
        ])->assertStatus(200);

        $this->assertSame('siap', $pesanan->fresh()->status_pesanan);

        $this->actingAs($kasir)->post('/kasir/pembayaran', [
            'kode_pesanan' => $pesanan->kode_pesanan,
            'metode_pembayaran' => 'tunai',
            'jumlah_bayar' => 100000,
        ])->assertRedirect();

        $pesanan->refresh();
        $this->assertSame('selesai', $pesanan->status_pesanan);
        $this->assertSame('sudah_bayar', $pesanan->status_pembayaran);
        $this->assertSame('kosong', $meja->fresh()->status_meja);
        $this->assertSame('lunas', Pembayaran::firstOrFail()->status_pembayaran);
    }
}