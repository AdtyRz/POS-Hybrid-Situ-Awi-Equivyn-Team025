<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KontrolAksesRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_tidak_bisa_masuk_halaman_admin(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_admin_bisa_masuk_halaman_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->getJson('/admin/dashboard')
            ->assertStatus(200);
    }

    public function test_pelanggan_tidak_bisa_masuk_halaman_admin(): void
    {
        $pelanggan = User::factory()->create(['role' => 'pelanggan']);

        $this->actingAs($pelanggan)
            ->getJson('/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_koki_bisa_masuk_kds_dapur(): void
    {
        $koki = User::factory()->create(['role' => 'koki']);

        $this->actingAs($koki)
            ->getJson('/kds/dapur')
            ->assertStatus(200);
    }

    public function test_koki_tidak_bisa_masuk_kds_bar(): void
    {
        $koki = User::factory()->create(['role' => 'koki']);

        $this->actingAs($koki)
            ->getJson('/kds/bar')
            ->assertStatus(403);
    }

    public function test_barista_bisa_masuk_kds_bar(): void
    {
        $barista = User::factory()->create(['role' => 'barista']);

        $this->actingAs($barista)
            ->getJson('/kds/bar')
            ->assertStatus(200);
    }

    public function test_pelayan_bisa_masuk_halaman_panggilan(): void
    {
        $pelayan = User::factory()->create(['role' => 'pelayan']);

        $this->actingAs($pelayan)
            ->getJson('/pelayan/panggilan')
            ->assertStatus(200);
    }

    public function test_kasir_bisa_masuk_halaman_kasir(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)
            ->getJson('/kasir/dashboard')
            ->assertStatus(200);
    }

    public function test_halaman_publik_tidak_perlu_login(): void
    {
        $this->getJson('/api/menu')->assertStatus(200);
        $this->getJson('/api/meja')->assertStatus(200);
    }

    public function test_admin_tidak_bisa_masuk_kds_dapur(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->getJson('/kds/dapur')
            ->assertStatus(403);
    }

    public function test_antrean_iot_hanya_untuk_pelayan(): void
    {
        $pelanggan = User::factory()->create(['role' => 'pelanggan']);

        $this->actingAs($pelanggan)
            ->getJson('/api/iot/antrean')
            ->assertStatus(403);
    }
}