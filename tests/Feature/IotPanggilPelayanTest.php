<?php

namespace Tests\Feature;

use App\Models\LogPanggilPelayan;
use App\Models\User;
use App\Models\MejaMakan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IotPanggilPelayanTest extends TestCase
{
    use RefreshDatabase;

    public function test_meja_dapat_mengirim_panggilan_pelayan(): void
    {
        $meja = $this->buatMeja();

        $response = $this->postJson('/api/iot/panggil-pelayan', [
            'kode_meja' => $meja->kode_meja,
            'qr_token' => $meja->qr_token,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'sukses');
        $response->assertJsonPath('data.kode_meja', $meja->kode_meja);
        $response->assertJsonPath('data.status_panggilan', 'menunggu');

        $this->assertDatabaseHas('log_panggil_pelayan', [
            'meja_id' => $meja->id,
            'status_panggilan' => 'menunggu',
        ]);
    }

    public function test_kode_meja_tidak_ada_ditolak(): void
    {
        $this->buatMeja();

        $response = $this->postJson('/api/iot/panggil-pelayan', [
            'kode_meja' => 'ZZZ-99',
            'qr_token' => 'TOKENAPASOAL',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('kode_meja');
    }

    public function test_qr_token_salah_ditolak(): void
    {
        $meja = $this->buatMeja();

        $response = $this->postJson('/api/iot/panggil-pelayan', [
            'kode_meja' => $meja->kode_meja,
            'qr_token' => 'TOKENPALSU',
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseCount('log_panggil_pelayan', 0);
    }

    public function test_data_kosong_ditolak(): void
    {
        $response = $this->postJson('/api/iot/panggil-pelayan', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kode_meja', 'qr_token']);
    }

    public function test_meja_tidak_bisa_menanggil_dua_kali(): void
    {
        $meja = $this->buatMeja();

        $this->postJson('/api/iot/panggil-pelayan', [
            'kode_meja' => $meja->kode_meja,
            'qr_token' => $meja->qr_token,
        ])->assertStatus(201);

        $response = $this->postJson('/api/iot/panggil-pelayan', [
            'kode_meja' => $meja->kode_meja,
            'qr_token' => $meja->qr_token,
        ]);

        $response->assertStatus(409);
        $this->assertDatabaseCount('log_panggil_pelayan', 1);
    }

    public function test_panggilan_selesai_membolehkan_panggilan_baru(): void
    {
        $meja = $this->buatMeja();

        $this->postJson('/api/iot/panggil-pelayan', [
            'kode_meja' => $meja->kode_meja,
            'qr_token' => $meja->qr_token,
        ])->assertStatus(201);

        LogPanggilPelayan::first()->update(['status_panggilan' => 'selesai']);

        $response = $this->postJson('/api/iot/panggil-pelayan', [
            'kode_meja' => $meja->kode_meja,
            'qr_token' => $meja->qr_token,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('log_panggil_pelayan', 2);
    }

    public function test_antrean_bisa_diakses_oleh_staf(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'pelayan']))
            ->getJson('/api/iot/antrean')
            ->assertStatus(200);
    }

    public function test_antrean_tertutup_untuk_tamu(): void
    {
        $this->getJson('/api/iot/antrean')
            ->assertStatus(401);
    }

    public function test_status_hanya_bisa_diubah_oleh_staf(): void
    {
        $meja = $this->buatMeja();

        $this->postJson('/api/iot/panggil-pelayan', [
            'kode_meja' => $meja->kode_meja,
            'qr_token' => $meja->qr_token,
        ])->assertStatus(201);

        $this->actingAs(User::factory()->create(['role' => 'pelayan']))
            ->patchJson('/api/iot/panggil-pelayan/1', ['status_panggilan' => 'ditangani'])
            ->assertStatus(200);

        $this->assertDatabaseHas('log_panggil_pelayan', [
            'id' => 1,
            'status_panggilan' => 'ditangani',
        ]);
    }

    public function test_status_tidak_valid_ditolak(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'pelayan']))
            ->patchJson('/api/iot/panggil-pelayan/1', ['status_panggilan' => 'ngawur'])
            ->assertStatus(422);
    }

    private function buatMeja(): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => 'LB-01',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 6,
            'qr_token' => 'QR-LB01RI8440',
            'status_meja' => 'kosong',
        ]);
    }
}