<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_check_tersedia_di_health(): void
    {
        $this->get('/health')
            ->assertOk();
    }

    public function test_alias_up_lama_tidak_lagi_dipakai(): void
    {
        $this->get('/up')
            ->assertNotFound();
    }
}
