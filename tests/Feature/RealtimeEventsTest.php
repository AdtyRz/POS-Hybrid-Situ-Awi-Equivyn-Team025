<?php

namespace Tests\Feature;

use App\Events\PesananBaru;
use App\Events\StatusPesananBerubah;
use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

class RealtimeEventsTest extends TestCase
{
    use RefreshDatabase;

    private function siapkan(): array
    {
        $meja = MejaMakan::create([
            'kode_meja' => 'LB-55',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'QR-LB-55-' . Str::random(20),
            'status_meja' => 'kosong',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan ' . Str::random(5),
            'target_kds' => 'dapur',
        ]);

        $menu = Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Realtime',
            'slug' => 'nasi-realtime-' . Str::random(5),
            'harga' => 20000,
            'stok_menu' => 20,
            'is_aktif' => true,
        ]);

        return [$meja, $menu];
    }

    public function test_api_pesanan_dan_perubahan_status_menyiarkan_event_realtime(): void
    {
        [$meja, $menu] = $this->siapkan();
        $kasir = User::factory()->create(['role' => 'kasir']);

        Event::fake([PesananBaru::class, StatusPesananBerubah::class]);

        $response = $this->postJson('/api/v1/pesanan', [
            'meja_id' => $meja->id,
            'qr_token' => $meja->qr_token,
            'jumlah' => [$menu->id => 1],
        ])->assertCreated();

        Event::assertDispatched(PesananBaru::class);

        $kode = $response->json('data.kode_pesanan');

        $this->actingAs($kasir)
            ->patchJson('/api/v1/pesanan/' . $kode . '/status', [
                'status_pesanan' => 'diproses',
            ])
            ->assertOk();

        Event::assertDispatched(StatusPesananBerubah::class);
    }

    public function test_api_pembayaran_menyiarkan_event_status(): void
    {
        [$meja, $menu] = $this->siapkan();
        $kasir = User::factory()->create(['role' => 'kasir']);

        $pesanan = app(\App\Services\PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'kasir', 'metode' => 'tunai'],
        );

        Event::fake([StatusPesananBerubah::class]);

        $this->actingAs($kasir)
            ->postJson('/api/v1/pembayaran', [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'tunai',
                'jumlah_bayar' => 20000,
            ])
            ->assertCreated();

        Event::assertDispatched(StatusPesananBerubah::class);
    }
}
