<?php

namespace Tests\Feature;

use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class QrisExpiryTest extends TestCase
{
    use RefreshDatabase;

    private function pesananQris(): Pesanan
    {
        $meja = MejaMakan::create([
            'kode_meja' => 'LB-78',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-uji-qris-expiry',
            'status_meja' => 'kosong',
        ]);

        return Pesanan::create([
            'kode_pesanan' => 'AWI-QRIS-EXPIRY-1',
            'meja_id' => $meja->id,
            'total_bayar' => 25000,
            'status_pesanan' => Pesanan::STATUS_MENUNGGU_VERIFIKASI,
            'status_pembayaran' => Pesanan::PAYMENT_PENDING_VERIFIKASI,
            'metode_pembayaran' => 'qris',
            'waktu_pesan' => now()->subMinute(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_batas_waktu_dari_midtrans_disimpan_dan_dipakai_hitung_mundur(): void
    {
        Carbon::setTestNow('2026-11-17 10:00:00');

        $pesanan = $this->pesananQris();
        $expiryTime = now()->addMinutes(7)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');

        $this->partialMock(MidtransService::class, function ($mock) use ($expiryTime) {
            $mock->shouldReceive('getTransactionStatus')
                ->once()
                ->andReturn((object) [
                    'transaction_status' => 'pending',
                    'transaction_id' => 'trx-uji-expiry',
                    'expiry_time' => $expiryTime,
                    'actions' => [],
                ]);
        });

        $hasil = app(MidtransService::class)->createQrisCharge($pesanan);
        $pesanan->refresh();

        $this->assertSame(420, $hasil['expires_in']);
        $this->assertNotNull($pesanan->qris_expires_at);
        $this->assertTrue($pesanan->qris_expires_at->equalTo(now()->addMinutes(7)));
        $this->assertSame($pesanan->qris_expires_at->toIso8601String(), $hasil['expires_at']);
    }

    public function test_batas_waktu_yang_sudah_tersimpan_tidak_digeser_saat_polling(): void
    {
        Carbon::setTestNow('2026-11-17 10:00:00');

        $pesanan = $this->pesananQris();
        $pesanan->forceFill(['qris_expires_at' => now()->addMinutes(2)])->save();

        $expiryTime = now()->addMinutes(7)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');

        $this->partialMock(MidtransService::class, function ($mock) use ($expiryTime) {
            $mock->shouldReceive('getTransactionStatus')
                ->once()
                ->andReturn((object) [
                    'transaction_status' => 'pending',
                    'transaction_id' => 'trx-uji-expiry',
                    'expiry_time' => $expiryTime,
                    'actions' => [],
                ]);
        });

        $hasil = app(MidtransService::class)->createQrisCharge($pesanan);
        $pesanan->refresh();

        $this->assertSame(120, $hasil['expires_in']);
        $this->assertTrue($pesanan->qris_expires_at->equalTo(now()->addMinutes(2)));
    }

    public function test_qris_expiry_dibatasi_lima_sampai_enam_puluh_menit(): void
    {
        $service = app(MidtransService::class);

        \App\Models\Pengaturan::simpanBanyak(['qris_expiry_minutes' => 1]);
        $this->assertSame(300, $service->qrisExpiryMinutes() * 60);

        \App\Models\Pengaturan::simpanBanyak(['qris_expiry_minutes' => 90]);
        $this->assertSame(3600, $service->qrisExpiryMinutes() * 60);
    }
}
