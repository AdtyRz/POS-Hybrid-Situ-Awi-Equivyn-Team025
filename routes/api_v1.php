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
    Route::post('/pesanan', [PesananApiController::class, 'store']);
    Route::get('/pesanan/{kode}', [PesananApiController::class, 'show']);
    Route::patch('/pesanan/{kode}/status', [PesananApiController::class, 'updateStatus']);
    Route::post('/pembayaran', [PembayaranApiController::class, 'store']);
    Route::get('/kds/antrean', [KdsApiController::class, 'antrean'])->middleware(['auth', 'role:koki,barista']);
    Route::get('/iot/panggilan', [IotApiController::class, 'panggilan']);
    Route::post('/iot/panggilan/{id}/tangani', [IotApiController::class, 'tangani'])->middleware(['auth', 'role:pelayan']);
});
