<?php

use App\Http\Controllers\Api\MidtransNotificationController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // Webhook Midtrans bersifat stateless. Didaftarkan di luar group
            // "web" agar tidak diblokir CSRF (Midtrans tidak mengirim token).
            Route::post('/midtrans/notification', [MidtransNotificationController::class, 'handle'])
                ->name('midtrans.notification');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // env() dipakai karena binding config belum tersedia saat callback ini
        // berjalan. Di produksi, set TRUSTED_PROXIES sebagai environment server.
        $trustedProxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', '127.0.0.1,::1'))
        )));

        $middleware->trustProxies(at: $trustedProxies);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\BatasiShift::class);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'aktif' => \App\Http\Middleware\CheckIsActive::class,
            'kasir.unlocked' => \App\Http\Middleware\EnsureKasirUnlocked::class,
            'iot.token' => \App\Http\Middleware\VerifyIotToken::class,
            'pesanan.no-store' => \App\Http\Middleware\NoStorePesanan::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
