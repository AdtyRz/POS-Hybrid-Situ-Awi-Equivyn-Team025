<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Models\User;
use App\Services\PesananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TransferPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function siapkanMejaDanMenu(): array
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan Utama',
            'target_kds' => 'dapur',
        ]);

        $meja = MejaMakan::create([
            'kode_meja' => 'LB-77',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-lb-77-transfer',
            'status_meja' => 'kosong',
        ]);

        $menu = Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Liwet Transfer',
            'slug' => 'nasi-liwet-transfer',
            'harga' => 25000,
            'stok_menu' => 10,
            'is_aktif' => true,
        ]);

        return [$meja, $menu];
    }

    private function sesiTerverifikasi(MejaMakan $meja): array
    {
        $sesi = SesiMeja::aktifUntuk($meja);

        return ['sesi_meja_' . $meja->id => $sesi->id];
    }

    private function aturTransfer(): void
    {
        Pengaturan::simpanBanyak([
            'transfer_bank' => 'BCA',
            'transfer_nomor_rekening' => '1234567890',
            'transfer_atas_nama' => 'Saung Situ Awi',
        ]);
    }

    private function kasir(): User
    {
        return User::factory()->create([
            'role' => 'kasir',
            'is_aktif' => true,
        ]);
    }

    private function pesananTransfer(MejaMakan $meja, Menu $menu): Pesanan
    {
        return app(PesananService::class)->buat(
            $meja,
            [$menu->id => 2],
            [],
            ['asal' => 'emenu', 'metode' => 'transfer', 'nama_pelanggan' => 'Budi Santoso']
        );
    }

    public function test_checkout_pelanggan_transfer_membuat_pesanan_menunggu_konfirmasi_kasir(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $this->aturTransfer();

        $response = $this->withSession($this->sesiTerverifikasi($meja))
            ->post(route('emenu.checkout', $meja->id), [
                'jumlah' => [$menu->id => 2],
                'metode' => 'transfer',
                'nama_pelanggan' => 'Budi Santoso',
            ]);

        $pesanan = Pesanan::firstOrFail();

        $response->assertRedirect();

        $this->assertSame('transfer', $pesanan->metode_pembayaran);
        $this->assertSame(Pesanan::STATUS_MENUNGGU_PEMBAYARAN, $pesanan->status_pesanan);
        $this->assertSame(Pesanan::PAYMENT_PENDING_PEMBAYARAN, $pesanan->status_pembayaran);

        $this->get((string) $response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Pembayaran Transfer Bank')
            ->assertSee('Menunggu Konfirmasi')
            ->assertSee('BCA')
            ->assertSee('1234567890')
            ->assertSee('Saung Situ Awi');
    }

    public function test_status_pesanan_transfer_menampilkan_rekening_dari_pengaturan(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $this->aturTransfer();
        $pesanan = $this->pesananTransfer($meja, $menu);

        $url = URL::temporarySignedRoute(
            'emenu.status',
            now()->addMinutes(10),
            ['kode_pesanan' => $pesanan->kode_pesanan],
        );

        $this->get($url)
            ->assertOk()
            ->assertSee('Total Transfer')
            ->assertSee('Rp50.000')
            ->assertSee('Bank')
            ->assertSee('BCA')
            ->assertSee('Nomor Rekening')
            ->assertSee('1234567890')
            ->assertSee('Atas Nama')
            ->assertSee('Saung Situ Awi')
            ->assertSee('Pesanan diproses setelah kasir mengonfirmasi dana transfer diterima.');
    }

    public function test_kasir_mengonfirmasi_transfer_via_json_tanpa_redirect(): void
    {
        $kasir = $this->kasir();
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $this->aturTransfer();
        $pesanan = $this->pesananTransfer($meja, $menu);

        $response = $this->actingAs($kasir)
            ->postJson(route('kasir.pembayaran.store'), [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'transfer',
                'jumlah_bayar' => (float) $pesanan->total_bayar,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('kode_pesanan', $pesanan->kode_pesanan)
            ->assertJsonPath('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR);

        $this->assertNull($response->headers->get('Location'));

        $pesanan->refresh();

        $this->assertSame(Pesanan::PAYMENT_SUDAH_BAYAR, $pesanan->status_pembayaran);
        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->status_pesanan);
        $this->assertSame(8, (int) $menu->fresh()->stok_menu);
        $this->assertDatabaseHas('pembayaran', [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'transfer',
            'status_pembayaran' => 'lunas',
        ]);
    }

    public function test_kasir_mengonfirmasi_transfer_melunasi_pesanan_dan_memotong_stok(): void
    {
        $kasir = $this->kasir();
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $this->aturTransfer();
        $pesanan = $this->pesananTransfer($meja, $menu);

        $this->actingAs($kasir)
            ->get(route('kasir.pembayaran.create', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertSee('Konfirmasi Transfer Bank')
            ->assertSee('1234567890');

        $this->actingAs($kasir)
            ->get(route('kasir.pembayaran.cari', ['kode_pembayaran' => $pesanan->kode_pesanan]))
            ->assertOk()
            ->assertSee('Konfirmasi Transfer Diterima')
            ->assertSee('1234567890');

        $this->actingAs($kasir)
            ->post(route('kasir.pembayaran.store'), [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'transfer',
                'jumlah_bayar' => 1,
            ])
            ->assertRedirect(route('kasir.dashboard', ['meja' => $meja->kode_meja]))
            ->assertSessionHas('sukses');

        $pesanan->refresh();

        $this->assertSame(Pesanan::PAYMENT_SUDAH_BAYAR, $pesanan->status_pembayaran);
        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->status_pesanan);
        $this->assertSame(8, (int) $menu->fresh()->stok_menu);

        $this->assertDatabaseHas('pembayaran', [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'transfer',
            'status_pembayaran' => 'lunas',
            'jumlah_bayar' => (float) $pesanan->total_bayar,
            'kembalian' => 0,
        ]);

        $this->actingAs($kasir)
            ->get(route('kasir.pembayaran.create', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertSee('Pesanan sudah dibayar');
    }

    public function test_kasir_tidak_bisa_mengonfirmasi_transfer_untuk_pesanan_non_transfer(): void
    {
        $kasir = $this->kasir();
        [$meja, $menu] = $this->siapkanMejaDanMenu();

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );

        $this->actingAs($kasir)
            ->from(route('kasir.pembayaran.create', $pesanan->kode_pesanan))
            ->post(route('kasir.pembayaran.store'), [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'transfer',
                'jumlah_bayar' => (float) $pesanan->total_bayar,
            ])
            ->assertRedirect(route('kasir.pembayaran.create', $pesanan->kode_pesanan))
            ->assertSessionHasErrors('metode_pembayaran');

        $this->assertSame(Pesanan::PAYMENT_PENDING_PEMBAYARAN, $pesanan->fresh()->status_pembayaran);
        $this->assertDatabaseMissing('pembayaran', ['pesanan_id' => $pesanan->id]);
    }

    public function test_api_pembayaran_menolak_pesanan_transfer(): void
    {
        $kasir = $this->kasir();
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $pesanan = $this->pesananTransfer($meja, $menu);

        $this->actingAs($kasir)
            ->postJson('/api/v1/pembayaran', [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'transfer',
                'jumlah_bayar' => (float) $pesanan->total_bayar,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'errors.metode_pembayaran.0',
                'Pesanan transfer wajib dikonfirmasi melalui dashboard kasir.'
            );

        $pesanan->refresh();

        $this->assertSame(Pesanan::PAYMENT_PENDING_PEMBAYARAN, $pesanan->status_pembayaran);
        $this->assertSame(Pesanan::STATUS_MENUNGGU_PEMBAYARAN, $pesanan->status_pesanan);
        $this->assertSame(8, (int) $menu->fresh()->stok_menu);
        $this->assertDatabaseMissing('pembayaran', ['pesanan_id' => $pesanan->id]);
    }
}
