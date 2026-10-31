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

class KasirPencarianTest extends TestCase
{
    use RefreshDatabase;

    private function kasir(): User
    {
        return User::create([
            'nama' => 'Kasir Pencarian',
            'email' => 'kasir-cari-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => 'kasir',
            'is_aktif' => true,
        ]);
    }

    private function meja(string $kode = 'LB-77'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-'.Str::lower($kode).'-abcdefghij',
            'status_meja' => 'kosong',
        ]);
    }

    private function menu(): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan '.uniqid(),
            'target_kds' => 'dapur',
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Pencarian '.uniqid(),
            'slug' => 'nasi-pencarian-'.uniqid(),
            'harga' => 25000,
            'stok_menu' => 20,
            'is_aktif' => true,
        ]);
    }

    private function pesanan(MejaMakan $meja, Menu $menu, string $nama, array $atribut = []): Pesanan
    {
        $pesanan = Pesanan::create(array_merge([
            'kode_pesanan' => 'SW-CARI-'.Str::upper(Str::random(8)),
            'kode_pembayaran' => 'SW-CARI-'.Str::upper(Str::random(8)),
            'meja_id' => $meja->id,
            'nama_pelanggan' => $nama,
            'total_bayar' => 25000,
            'status_pesanan' => Pesanan::STATUS_MENUNGGU_PEMBAYARAN,
            'status_pembayaran' => Pesanan::PAYMENT_PENDING_PEMBAYARAN,
            'metode_pembayaran' => 'tunai',
        ], $atribut));

        $pesanan->detailPesanan()->create([
            'menu_id' => $menu->id,
            'jumlah' => 1,
            'harga_satuan' => 25000,
            'subtotal' => 25000,
            'status_item' => 'menunggu',
        ]);

        return $pesanan;
    }

    public function test_kasir_dapat_mencari_beberapa_pesanan_dari_kode_meja_dan_nama(): void
    {
        $meja = $this->meja();
        $menu = $this->menu();

        $this->pesanan($meja, $menu, 'Budi Santoso');
        $this->pesanan($meja, $menu, 'Siti Aminah');
        $this->pesanan($meja, $menu, 'Sudah Bayar', [
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
        ]);

        $this->actingAs($this->kasir())
            ->get(route('kasir.pembayaran.cari', ['kode_pembayaran' => 'LB-77']))
            ->assertOk()
            ->assertSee('Hasil Pencarian')
            ->assertSee('Budi Santoso')
            ->assertSee('Siti Aminah')
            ->assertDontSee('Sudah Bayar');
    }

    public function test_kasir_dapat_mencari_pesanan_berdasarkan_nama_pemesan(): void
    {
        $meja = $this->meja();
        $menu = $this->menu();

        $this->pesanan($meja, $menu, 'Budi Santoso');

        $this->actingAs($this->kasir())
            ->get(route('kasir.pembayaran.cari', ['kode_pembayaran' => 'Budi']))
            ->assertOk()
            ->assertSee('Meja LB-77')
            ->assertSee('Budi Santoso')
            ->assertSee('Proses Pembayaran Tunai');
    }

    public function test_pencarian_kode_pesanan_yang_sudah_lunas_memberi_pesan_jelas(): void
    {
        $meja = $this->meja();
        $menu = $this->menu();

        $pesanan = $this->pesanan($meja, $menu, 'Budi Santoso', [
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
        ]);

        $this->actingAs($this->kasir())
            ->get(route('kasir.pembayaran.cari', ['kode_pembayaran' => $pesanan->kode_pesanan]))
            ->assertOk()
            ->assertSee('Pesanan ini sudah dibayar');
    }
}
