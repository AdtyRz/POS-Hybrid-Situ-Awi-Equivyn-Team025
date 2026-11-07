<?php

namespace Tests\Feature;

use App\Events\AdminDiperbarui;
use App\Events\MenuDiperbarui;
use App\Events\PanggilanPelayan;
use App\Events\PengaturanDiperbarui;
use App\Events\PesananBaru;
use App\Events\SesiMejaBerubah;
use App\Events\StatusPesananBerubah;
use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\SesiMeja;
use App\Models\User;
use App\Services\PesananService;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BroadcastChannelSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-app',
            'broadcasting.connections.reverb.options' => [
                'cluster' => 'mt1',
                'host' => 'localhost',
                'port' => 8080,
                'scheme' => 'http',
                'useTLS' => false,
            ],
        ]);

        require base_path('routes/channels.php');
    }

    private function meja(string $kode = 'LB-77'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'QR-'.$kode.'-'.Str::random(20),
            'status_meja' => 'kosong',
        ]);
    }

    private function pesanan(MejaMakan $meja): \App\Models\Pesanan
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan '.Str::random(5),
            'target_kds' => 'dapur',
        ]);

        $menu = Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Uji',
            'slug' => 'nasi-uji-'.Str::random(5),
            'harga' => 20000,
            'stok_menu' => 20,
            'is_aktif' => true,
        ]);

        return app(PesananService::class)->buat($meja, [$menu->id => 1], [], ['asal' => 'api']);
    }

    private function otorisasi(string $channel, ?User $user = null, array $session = [])
    {
        if ($user !== null) {
            $this->actingAs($user);
        }

        return $this->withSession($session)->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => $channel,
        ]);
    }

    public function test_tamu_tidak_bisa_mendengarkan_channel_staf(): void
    {
        $this->otorisasi('private-admin')->assertForbidden();
        $this->otorisasi('private-kasir')->assertForbidden();
        $this->otorisasi('private-pelayan')->assertForbidden();
        $this->otorisasi('private-kds.dapur')->assertForbidden();
    }

    public function test_kasir_hanya_bisa_mendengarkan_channel_kasir_dan_menu(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir', 'is_aktif' => true]);

        $this->otorisasi('private-kasir', $kasir)->assertOk();
        $this->otorisasi('private-menu', $kasir)->assertOk();
        $this->otorisasi('private-admin', $kasir)->assertForbidden();
        $this->otorisasi('private-kds.dapur', $kasir)->assertForbidden();
    }

    public function test_staf_hanya_bisa_mendengarkan_channel_sesuai_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_aktif' => true]);
        $koki = User::factory()->create(['role' => 'koki', 'is_aktif' => true]);

        $this->otorisasi('private-admin', $admin)->assertOk();
        $this->otorisasi('private-kds.dapur', $admin)->assertOk();
        $this->otorisasi('private-kds.dapur', $koki)->assertOk();
        $this->otorisasi('private-kds.bar', $koki)->assertForbidden();
    }

    public function test_tamu_dengan_sesi_meja_aktif_bisa_mendengarkan_channel_meja_dan_pesanan_sesi_itu(): void
    {
        $meja = $this->meja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $pesanan = $this->pesanan($meja);
        $session = ['sesi_meja_'.$meja->id => $sesi->id];

        $this->otorisasi('private-meja.'.$meja->kode_meja, null, $session)->assertOk();
        $this->otorisasi('private-pesanan.'.$pesanan->kode_pesanan, null, $session)->assertOk();
    }

    public function test_tamu_tanpa_sesi_tidak_bisa_mendengarkan_channel_meja_atau_pesanan(): void
    {
        $meja = $this->meja();
        SesiMeja::aktifUntuk($meja);
        $pesanan = $this->pesanan($meja);

        $this->otorisasi('private-meja.'.$meja->kode_meja)->assertForbidden();
        $this->otorisasi('private-pesanan.'.$pesanan->kode_pesanan)->assertForbidden();
    }

    public function test_semua_event_realtime_hanya_disiarkan_ke_channel_privat(): void
    {
        $events = [
            new PesananBaru(['kode_pesanan' => 'SW-1', 'meja' => 'LB-77']),
            new StatusPesananBerubah(['kode_pesanan' => 'SW-1', 'meja' => 'LB-77']),
            new SesiMejaBerubah('LB-77', 1),
            new PanggilanPelayan(['meja' => 'LB-77']),
            new MenuDiperbarui(),
            new PengaturanDiperbarui(),
            new AdminDiperbarui(),
        ];

        foreach ($events as $event) {
            foreach ($event->broadcastOn() as $channel) {
                $this->assertInstanceOf(PrivateChannel::class, $channel);
            }
        }
    }
}
