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

    public function test_alias_health_tetap_tersedia(): void
    {
        $this->get('/health')
            ->assertOk();
    }

    public function test_health_check_dan_aliasnya_mendukung_json(): void
    {
        $this->getJson('/aktif')
            ->assertOk()
            ->assertJson(['status' => 'up']);

        $this->getJson('/health')
            ->assertOk()
            ->assertJson(['status' => 'up']);
    }

    public function test_alias_up_lama_tidak_lagi_dipakai(): void
    {
        $this->get('/up')
            ->assertNotFound();
    }
}
