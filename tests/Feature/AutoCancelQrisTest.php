<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoCancelQrisTest extends TestCase
{
    use RefreshDatabase;

    private ?MejaMakan $meja = null;

    private function meja(): MejaMakan
    {
        return $this->meja ??= MejaMakan::create([
            'kode_meja' => 'LB-77',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-qris-kedaluwarsa',
            'status_meja' => 'kosong',
        ]);
    }

    private function pesananQris(array $atribut = []): Pesanan
    {
        return Pesanan::create(array_merge([
            'kode_pesanan' => 'AWI-QRIS-UJI-' . strtoupper(bin2hex(random_bytes(4))),
            'meja_id' => $this->meja()->id,
            'total_bayar' => 25000,
            'status_pesanan' => Pesanan::STATUS_MENUNGGU_VERIFIKASI,
            'status_pembayaran' => Pesanan::PAYMENT_PENDING_VERIFIKASI,
            'metode_pembayaran' => 'qris',
            'waktu_pesan' => now()->subMinutes(10),
            'qris_expires_at' => now()->subMinute(),
        ], $atribut));
    }

    public function test_pesanan_qris_kedaluwarsa_dibatalkan_saat_opsi_aktif(): void
    {
        Pengaturan::simpanBanyak(['midtrans_auto_cancel' => true]);

        $pesanan = $this->pesananQris();

        $this->artisan('pesanan:batalkan-kedaluwarsa')
            ->expectsOutputToContain('Dibatalkan: 1')
            ->assertExitCode(0);

        $pesanan->refresh();

        $this->assertSame(Pesanan::STATUS_DIBATALKAN, $pesanan->status_pesanan);
        $this->assertNotNull($pesanan->void_at);
        $this->assertStringContainsString('QRIS', (string) $pesanan->alasan_void);
    }

    public function test_pesanan_qris_kedaluwarsa_tidak_dibatalkan_saat_opsi_nonaktif(): void
    {
        Pengaturan::simpanBanyak(['midtrans_auto_cancel' => false]);

        $pesanan = $this->pesananQris();

        $this->artisan('pesanan:batalkan-kedaluwarsa')
            ->expectsOutputToContain('nonaktif')
            ->assertExitCode(0);

        $this->assertSame(Pesanan::STATUS_MENUNGGU_VERIFIKASI, $pesanan->fresh()->status_pesanan);
        $this->assertNull($pesanan->fresh()->void_at);
    }

    public function test_pesanan_belum_kedaluwarsa_atau_sudah_lunas_tidak_dibatalkan(): void
    {
        Pengaturan::simpanBanyak(['midtrans_auto_cancel' => true]);

        $belumKedaluwarsa = $this->pesananQris([
            'qris_expires_at' => now()->addMinutes(5),
        ]);

        $sudahLunas = $this->pesananQris([
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'qris_expires_at' => now()->subMinute(),
        ]);

        $this->artisan('pesanan:batalkan-kedaluwarsa')
            ->expectsOutputToContain('Dibatalkan: 0')
            ->assertExitCode(0);

        $this->assertSame(Pesanan::STATUS_MENUNGGU_VERIFIKASI, $belumKedaluwarsa->fresh()->status_pesanan);
        $this->assertSame(Pesanan::STATUS_DIPROSES, $sudahLunas->fresh()->status_pesanan);
    }

    public function test_pesanan_non_qris_tidak_ikut_dibatalkan(): void
    {
        Pengaturan::simpanBanyak(['midtrans_auto_cancel' => true]);

        $pesanan = $this->pesananQris([
            'metode_pembayaran' => 'tunai',
            'status_pesanan' => Pesanan::STATUS_MENUNGGU_PEMBAYARAN,
            'status_pembayaran' => Pesanan::PAYMENT_PENDING_PEMBAYARAN,
        ]);

        $this->artisan('pesanan:batalkan-kedaluwarsa')
            ->expectsOutputToContain('Dibatalkan: 0')
            ->assertExitCode(0);

        $this->assertSame(Pesanan::STATUS_MENUNGGU_PEMBAYARAN, $pesanan->fresh()->status_pesanan);
    }

    public function test_auto_cancel_qris_mengembalikan_stok_yang_dipotong_saat_checkout(): void
    {
        Pengaturan::simpanBanyak(['midtrans_auto_cancel' => true]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan Auto Cancel',
            'target_kds' => 'dapur',
        ]);

        $menu = Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Auto Cancel',
            'slug' => 'nasi-auto-cancel',
            'harga' => 25000,
            'stok_menu' => 10,
            'is_aktif' => true,
        ]);

        $pesanan = app(PesananService::class)->buat(
            $this->meja(),
            [$menu->id => 2],
            [],
            ['asal' => 'emenu', 'metode' => 'qris', 'nama_pelanggan' => 'Tamu QRIS'],
        );

        $pesanan->update(['qris_expires_at' => now()->subMinute()]);

        $this->assertSame(8, (int) $menu->fresh()->stok_menu);

        $this->artisan('pesanan:batalkan-kedaluwarsa')
            ->expectsOutputToContain('Dibatalkan: 1')
            ->assertExitCode(0);

        $pesanan->refresh();

        $this->assertSame(Pesanan::STATUS_DIBATALKAN, $pesanan->status_pesanan);
        $this->assertSame(10, (int) $menu->fresh()->stok_menu);
        $this->assertDatabaseHas('stok_mutasi', [
            'pesanan_id' => $pesanan->id,
            'jenis' => 'pembatalan',
            'jumlah_delta' => 2,
        ]);
    }
}
