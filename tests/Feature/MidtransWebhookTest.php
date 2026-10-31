<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_midtrans_tidak_berada_di_group_web_berbasis_csrf(): void
    {
        $route = Route::getRoutes()->getByName('midtrans.notification');

        $this->assertNotNull($route);
        $this->assertSame('midtrans/notification', $route->uri());
        $this->assertNotContains(PreventRequestForgery::class, $route->gatherMiddleware());
    }

    public function test_webhook_midtrans_menolak_signature_tidak_valid(): void
    {
        config(['midtrans.server_key' => 'server-key-uji']);

        $response = $this->postJson('/midtrans/notification', [
            'order_id' => 'SW-UJI',
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'signature_key' => 'tidak-valid',
        ]);

        $response->assertStatus(403)
            ->assertJson(['status' => 'error']);
    }

    public function test_webhook_midtrans_dengan_signature_valid_diproses(): void
    {
        config(['midtrans.server_key' => 'server-key-uji']);

        $payload = [
            'order_id' => 'SW-UJI-TIDAK-ADA',
            'status_code' => '200',
            'gross_amount' => '10000.00',
        ];
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'server-key-uji'
        );

        $response = $this->postJson('/midtrans/notification', $payload);

        $response->assertNotFound()
            ->assertJson(['status' => 'error', 'message' => 'Order not found']);
    }
}
