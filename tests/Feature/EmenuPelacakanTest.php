<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmenuPelacakanTest extends TestCase
{
    use RefreshDatabase;

    private function siapkanMejaDanMenu(): array
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan Utama',
            'target_kds' => 'dapur',
        ]);

        $meja = MejaMakan::create([
            'kode_meja' => 'LB-99',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-lb-99-abcdefghijklmnop',
            'status_meja' => 'kosong',
        ]);

        $menu = Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Liwet Uji',
            'slug' => 'nasi-liwet-uji',
            'harga' => 25000,
            'stok_menu' => 10,
        ]);

        return [$meja, $menu];
    }

    public function test_checkout_menyimpan_riwayat_ke_session(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();

        $response = $this->post(route('emenu.checkout', $meja->id), [
            'jumlah' => [$menu->id => 2],
            'catatan' => [$menu->id => 'pedas'],
            'metode' => 'tunai',
        ]);

        $response->assertSessionHas('riwayat_meja_' . $meja->id);

        $pesanan = Pesanan::first();
        $this->assertNotNull($pesanan);
        $this->assertSame('terisi', $meja->fresh()->status_meja);
        $this->assertSame(8, $menu->fresh()->stok_menu);
    }

    public function test_tombol_pelacakan_muncul_saat_ada_riwayat(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();

        $this->get(route('emenu.meja', $meja->id))
            ->assertOk()
            ->assertDontSee('Pelacakan &amp; Status Pesanan', false);

        $this->withSession(['riwayat_meja_' . $meja->id => ['SW-UJI-01']])
            ->get(route('emenu.meja', $meja->id))
            ->assertOk()
            ->assertSee('Pelacakan &amp; Status Pesanan', false);
    }

    public function test_halaman_pelacakan_menampilkan_riwayat_pesanan(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();

        $pesanan = Pesanan::create([
            'kode_pesanan' => 'SW-UJI-01',
            'meja_id' => $meja->id,
            'total_bayar' => 55000,
            'metode_pembayaran' => 'tunai',
        ]);
        $pesanan->detailPesanan()->create([
            'menu_id' => $menu->id,
            'jumlah' => 2,
            'harga_satuan' => 25000,
            'subtotal' => 50000,
        ]);

        $this->withSession(['riwayat_meja_' . $meja->id => ['SW-UJI-01']])
            ->get(route('emenu.pelacakan', $meja->id))
            ->assertOk()
            ->assertSee('SW-UJI-01')
            ->assertSee('2 item');
    }

    public function test_halaman_pelacakan_kosong_tanpa_sesi(): void
    {
        [$meja] = $this->siapkanMejaDanMenu();

        $this->get(route('emenu.pelacakan', $meja->id))
            ->assertOk()
            ->assertSee('Belum ada pesanan pada sesi ini');
    }
}
