<?php

namespace Tests\Feature;

use App\Events\AdminDiperbarui;
use App\Events\PanggilanPelayan;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminRealtimeNoRefreshTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_aktif' => true,
        ]);
    }

    private function meja(string $kode = 'LB-77'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'QR-' . Str::random(20),
            'status_meja' => 'kosong',
        ]);
    }

    private function ajax()
    {
        return $this->withHeaders([
            'Accept' => 'application/json',
            'X-Admin-Ajax' => '1',
        ]);
    }

    public function test_simpan_admin_ajax_mengembalikan_json_tanpa_redirect(): void
    {
        $admin = $this->admin();
        $meja = $this->meja();

        $response = $this->actingAs($admin)
            ->ajax()
            ->post(route('admin.master-meja.selesaikan-panggilan'), [
                'kode_meja' => $meja->kode_meja,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Panggilan saung LB-77 diselesaikan.');

        $this->assertArrayNotHasKey('errors', $response->json());
    }

    public function test_simpan_admin_tanpa_header_ajax_tetap_redirect(): void
    {
        $admin = $this->admin();
        $meja = $this->meja();

        $this->actingAs($admin)
            ->post(route('admin.master-meja.selesaikan-panggilan'), [
                'kode_meja' => $meja->kode_meja,
            ])
            ->assertRedirect(route('admin.master-meja'))
            ->assertSessionHas('success');
    }

    public function test_galat_validasi_admin_ajax_menjadi_422_json(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->ajax()
            ->post(route('admin.meja.simpan'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['kode_meja', 'area']);
    }

    public function test_galat_bisnis_admin_ajax_menjadi_json_422(): void
    {
        $admin = $this->admin();
        $this->meja('LB-88');

        $this->actingAs($admin)
            ->ajax()
            ->post(route('admin.meja.simpan'), [
                'kode_meja' => 'LB-88',
                'area' => 'Lesehan Bawah',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Kode meja sudah digunakan.');
    }

    public function test_menutup_panggilan_menyiarkan_status_selesai(): void
    {
        $admin = $this->admin();
        $meja = $this->meja();
        $panggilan = LogPanggilPelayan::create([
            'meja_id' => $meja->id,
            'status_panggilan' => 'menunggu',
        ]);

        Event::fake([PanggilanPelayan::class]);

        $this->actingAs($admin)
            ->ajax()
            ->post(route('admin.master-meja.selesaikan-panggilan'), [
                'kode_meja' => $meja->kode_meja,
            ])
            ->assertOk();

        Event::assertDispatched(PanggilanPelayan::class, function (PanggilanPelayan $event) use ($meja, $panggilan) {
            return $event->panggilan['id'] === $panggilan->id
                && $event->panggilan['meja_id'] === $meja->id
                && $event->panggilan['status_panggilan'] === 'selesai';
        });

        $this->assertNotNull($panggilan->fresh()->waktu_selesai);
    }

    public function test_perubahan_meja_dan_akun_menyiarkan_admin_diperbarui(): void
    {
        $admin = $this->admin();

        Event::fake([AdminDiperbarui::class]);

        $this->actingAs($admin)
            ->ajax()
            ->post(route('admin.meja.simpan'), [
                'kode_meja' => 'LB-99',
                'area' => 'Lesehan Atas',
                'kapasitas' => 4,
            ])
            ->assertOk();

        Event::assertDispatched(AdminDiperbarui::class, fn (AdminDiperbarui $event) => $event->jenis === 'meja');

        Event::fake([AdminDiperbarui::class]);

        $this->actingAs($admin)
            ->ajax()
            ->post(route('admin.akun.simpan'), [
                'nama' => 'Staf Baru',
                'email' => 'staf-' . Str::lower(Str::random(6)) . '@situawi.test',
                'password' => 'rahasia123',
                'role' => 'pelayan',
            ])
            ->assertOk();

        Event::assertDispatched(AdminDiperbarui::class, fn (AdminDiperbarui $event) => $event->jenis === 'akun');
    }

    public function test_pengaturan_admin_ajax_mengembalikan_json(): void
    {
        $admin = $this->admin();

        Event::fake([\App\Events\PengaturanDiperbarui::class]);

        $payload = [
            'nama_resto' => 'Saung Situ Awi',
            'qris_expiry_minutes' => 15,
            'session_expiry_minutes' => 60,
            'shift_1_aktif' => true,
            'shift_1_mulai' => '08:00',
            'shift_1_selesai' => '16:00',
        ];

        $this->actingAs($admin)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Admin-Ajax' => '1',
            ])
            ->postJson(route('admin.pengaturan-sistem.simpan'), $payload)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Pengaturan sistem berhasil disimpan.');
    }

    public function test_unggah_logo_admin_ajax_mengembalikan_json_dan_url_baru(): void
    {
        $admin = $this->admin();
        $sebelum = glob(public_path('asset/resto/*')) ?: [];

        try {
            $response = $this->actingAs($admin)
                ->ajax()
                ->post(route('admin.pengaturan-sistem.logo'), [
                    'logo' => UploadedFile::fake()->image('logo-resto.png', 200, 120),
                ]);

            $response->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonStructure(['message', 'logo_url']);

            $this->assertStringContainsString('asset/resto/', $response->json('logo_url'));
        } finally {
            $sesudah = glob(public_path('asset/resto/*')) ?: [];

            foreach (array_diff($sesudah, $sebelum) as $berkas) {
                @unlink($berkas);
            }
        }
    }
}
