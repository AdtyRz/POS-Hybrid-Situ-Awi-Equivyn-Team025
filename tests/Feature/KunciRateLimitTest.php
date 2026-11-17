<?php

namespace Tests\Feature;

use App\Models\MejaMakan;
use App\Models\SesiMeja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KunciRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_kunci_menampilkan_hitung_mundur_dan_mengirim_retry_after(): void
    {
        $meja = MejaMakan::create([
            'kode_meja' => 'LB-79',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-uji-rate-limit',
            'status_meja' => 'kosong',
        ]);

        SesiMeja::aktifUntuk($meja);

        $this->get(route('emenu.kunci', $meja->kode_meja))
            ->assertOk()
            ->assertSee('Coba lagi dalam')
            ->assertSee('Retry-After', false);

        for ($percobaan = 0; $percobaan < 5; $percobaan++) {
            $this->postJson(route('emenu.buka', $meja->kode_meja), ['kode_unik' => '000000'])
                ->assertStatus(422);
        }

        $response = $this->postJson(route('emenu.buka', $meja->kode_meja), ['kode_unik' => '000000'])
            ->assertStatus(429);

        $this->assertNotNull($response->headers->get('Retry-After'));
        $this->assertGreaterThan(0, (int) $response->headers->get('Retry-After'));
    }

    public function test_form_kode_di_beranda_mendapat_hitung_mundur_saat_kena_rate_limit(): void
    {
        for ($percobaan = 0; $percobaan < 5; $percobaan++) {
            $this->from(route('emenu.index'))
                ->post(route('emenu.masuk'), ['kode_unik' => '000000'])
                ->assertRedirect(route('emenu.index'));
        }

        $response = $this->from(route('emenu.index'))
            ->post(route('emenu.masuk'), ['kode_unik' => '000000']);

        $response->assertRedirect(route('emenu.index'));
        $response->assertSessionHasErrors('kode_unik');
        $response->assertSessionHas('batas_coba_lagi');

        $this->get(route('emenu.index'))
            ->assertOk()
            ->assertSee('hitungMundurKode', false)
            ->assertSee('Coba lagi dalam');
    }
}
