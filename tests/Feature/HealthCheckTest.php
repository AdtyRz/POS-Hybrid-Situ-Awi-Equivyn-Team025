<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_check_tersedia_di_aktif(): void
    {
        $this->get('/aktif')
            ->assertOk();
    }

    public function test_health_lama_tidak_lagi_dipakai(): void
    {
        $this->get('/health')
            ->assertNotFound();
    }

    public function test_health_check_mendukung_json(): void
    {
        $this->getJson('/aktif')
            ->assertOk()
            ->assertJson(['status' => 'up']);
    }

    public function test_up_lama_tidak_lagi_dipakai(): void
    {
        $this->get('/up')
            ->assertNotFound();
    }
}
