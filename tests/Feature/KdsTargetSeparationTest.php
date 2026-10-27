<?php

namespace Tests\Feature;

use App\Models\DetailPesanan;
use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class KdsTargetSeparationTest extends TestCase
{
    use RefreshDatabase;

    private function meja(): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => 'LB-31',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'QR-LB-31-' . Str::random(20),
            'status_meja' => 'terisi',
        ]);
    }

    private function menu(string $target, string $nama): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori ' . $nama . ' ' . Str::random(4),
            'target_kds' => $target,
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => $nama,
            'slug' => Str::slug($nama) . '-' . Str::random(4),
            'harga' => 20000,
            'stok_menu' => 20,
            'is_aktif' => true,
        ]);
    }

    private function pesananDuaStasiun(): array
    {
        $meja = $this->meja();
        $menuDapur = $this->menu('dapur', 'Nasi Liwet');
        $menuBar = $this->menu('bar', 'Es Teh');

        $pesanan = Pesanan::create([
            'kode_pesanan' => 'SW-KDS-' . Str::upper(Str::random(6)),
            'meja_id' => $meja->id,
            'total_bayar' => 40000,
            'status_pesanan' => Pesanan::STATUS_MENUNGGU,
            'status_pembayaran' => Pesanan::PAYMENT_BELUM_BAYAR,
        ]);

        $detailDapur = $pesanan->detailPesanan()->create([
            'menu_id' => $menuDapur->id,
            'jumlah' => 1,
            'harga_satuan' => 20000,
            'subtotal' => 20000,
            'status_item' => 'menunggu',
        ]);

        $detailBar = $pesanan->detailPesanan()->create([
            'menu_id' => $menuBar->id,
            'jumlah' => 1,
            'harga_satuan' => 20000,
            'subtotal' => 20000,
            'status_item' => 'menunggu',
        ]);

        return [$pesanan, $detailDapur, $detailBar];
    }

    public function test_api_kds_memfilter_antrean_sesuai_stasiun_dan_menolak_target_lain(): void
    {
        [$pesanan] = $this->pesananDuaStasiun();

        $koki = User::factory()->create(['role' => 'koki']);
        $barista = User::factory()->create(['role' => 'barista']);

        $this->getJson('/api/v1/kds/antrean')->assertStatus(401);

        $this->actingAs($koki)
            ->getJson('/api/v1/kds/antrean')
            ->assertOk()
            ->assertJsonPath('target', 'dapur')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.menu.nama_menu', 'Nasi Liwet');

        $this->actingAs($barista)
            ->getJson('/api/v1/kds/antrean')
            ->assertOk()
            ->assertJsonPath('target', 'bar')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.menu.nama_menu', 'Es Teh');

        $this->actingAs($koki)
            ->getJson('/api/v1/kds/antrean?target=bar')
            ->assertForbidden();

        $this->actingAs($koki)
            ->getJson('/api/v1/kds/antrean?target=minuman')
            ->assertStatus(422);

        $this->actingAs($koki)
            ->getJson('/api/v1/kds/antrean?target=dapur')
            ->assertOk()
            ->assertJsonPath('target', 'dapur');
    }

    public function test_koki_tidak_bisa_mengubah_item_milik_stasiun_bar(): void
    {
        [$pesanan, $detailDapur, $detailBar] = $this->pesananDuaStasiun();
        $koki = User::factory()->create(['role' => 'koki']);

        $this->actingAs($koki)
            ->patch(route('kds.dapur.ubah', $pesanan->id), [
                'aksi' => 'item',
                'item_id' => $detailBar->id,
            ])
            ->assertNotFound();

        $this->assertSame('menunggu', $detailBar->fresh()->status_item);
        $this->assertSame('menunggu', $detailDapur->fresh()->status_item);

        $this->actingAs($koki)
            ->patch(route('kds.dapur.ubah', $pesanan->id), [
                'aksi' => 'item',
                'item_id' => $detailDapur->id,
            ])
            ->assertRedirect();

        $this->assertSame('diproses', $detailDapur->fresh()->status_item);
        $this->assertSame('menunggu', $detailBar->fresh()->status_item);
    }
}
