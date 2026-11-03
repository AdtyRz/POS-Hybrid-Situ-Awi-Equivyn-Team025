<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('admin@situawi.com');
        $response->assertDontSee('pilih peran cepat');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_sesi_login_web_dapat_memakai_api_v1_tanpa_token_sanctum(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        // statefulApi() menambahkan middleware sesi Sanctum ke grup api.
        $apiMiddleware = app(\Illuminate\Routing\Router::class)->getMiddlewareGroups()['api'];
        $this->assertContains(
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            $apiMiddleware,
        );

        // Cookie sesi first-party harus diterima sebelum validasi berjalan.
        // Tanpa middleware stateful, request berakhir 401.
        $this->postJson('/api/v1/pembayaran', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['kode_pesanan']);
    }
}
