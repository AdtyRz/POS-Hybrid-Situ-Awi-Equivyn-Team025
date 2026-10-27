<?php

use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\MejaController;
use App\Http\Controllers\Api\PesananApiController;
use App\Http\Controllers\Api\PembayaranApiController;
use App\Http\Controllers\Api\KdsApiController;
use App\Http\Controllers\Api\IotApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/menu', [MenuController::class, 'index']);
    Route::get('/meja', [MejaController::class, 'index']);

    // Endpoint pelanggan. Detail pesanan tetap wajib membawa bukti sah
    // (staf login, qr_token meja, atau kode saung aktif).
    Route::middleware('pesanan.no-store')->group(function () {
        Route::post('/pesanan', [PesananApiController::class, 'store'])
            ->middleware('throttle:20,1');
        Route::get('/pesanan/{kode}', [PesananApiController::class, 'show'])
            ->middleware('throttle:30,1');
    });

    Route::middleware(['auth', 'aktif'])->group(function () {
        Route::middleware('role:koki,barista,pelayan,admin,kasir')->group(function () {
            Route::patch('/pesanan/{kode}/status', [PesananApiController::class, 'updateStatus'])
                ->middleware('throttle:60,1');
        });

        Route::middleware('role:kasir,admin')->group(function () {
            Route::post('/pembayaran', [PembayaranApiController::class, 'store'])
                ->middleware('throttle:30,1');
        });

        Route::middleware('role:pelayan,admin')->group(function () {
            Route::get('/iot/panggilan', [IotApiController::class, 'panggilan']);
        });
    });

    Route::middleware(['auth', 'aktif', 'role:koki,barista'])->group(function () {
        Route::get('/kds/antrean', [KdsApiController::class, 'antrean'])
            ->middleware('throttle:120,1');
    });

    Route::middleware(['auth', 'role:pelayan'])->group(function () {
        Route::post('/iot/panggilan/{id}/tangani', [IotApiController::class, 'tangani']);
    });
});
