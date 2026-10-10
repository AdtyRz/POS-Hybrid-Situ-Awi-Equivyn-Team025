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

    // Public endpoint for customers - validates qr_token
    Route::post('/pesanan', [PesananApiController::class, 'store']);
    Route::get('/pesanan/{kode}', [PesananApiController::class, 'show']);

    Route::middleware('auth')->group(function () {
        Route::middleware('role:koki,barista,pelayan,admin,kasir')->group(function () {
            Route::patch('/pesanan/{kode}/status', [PesananApiController::class, 'updateStatus']);
        });

        Route::middleware('role:kasir,admin')->group(function () {
            Route::post('/pembayaran', [PembayaranApiController::class, 'store']);
        });

        Route::middleware('role:pelayan,admin')->group(function () {
            Route::get('/iot/panggilan', [IotApiController::class, 'panggilan']);
        });
    });

    Route::middleware(['auth', 'role:koki,barista'])->group(function () {
        Route::get('/kds/antrean', [KdsApiController::class, 'antrean']);
    });

    Route::middleware(['auth', 'role:pelayan'])->group(function () {
        Route::post('/iot/panggilan/{id}/tangani', [IotApiController::class, 'tangani']);
    });
});
