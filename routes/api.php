<?php

use App\Http\Controllers\EmenuController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\IotController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthCheckController::class, 'index']);

Route::get('/menu', [EmenuController::class, 'menu']);

Route::get('/meja', [EmenuController::class, 'meja']);

Route::post('/iot/panggil-pelayan', [IotController::class, 'panggilPelayan']);

Route::middleware(['auth', 'role:pelayan'])->group(function () {
    Route::get('/iot/antrean', [IotController::class, 'antrean']);
    Route::patch('/iot/panggil-pelayan/{id}', [IotController::class, 'ubahStatus']);
});