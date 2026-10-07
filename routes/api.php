<?php

use App\Http\Controllers\EMenuController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\IotController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthCheckController::class, 'index']);

Route::get('/menu', [EMenuController::class, 'menu']);
Route::get('/meja', [EMenuController::class, 'daftarMeja']);

Route::get('/iot/status-meja', [IotController::class, 'statusMeja']);
Route::post('/iot/panggil-pelayan', [IotController::class, 'panggilPelayan']);

Route::middleware(['auth', 'role:pelayan'])->group(function () {
    Route::get('/iot/antrean', [IotController::class, 'antrean']);
    Route::patch('/iot/panggil-pelayan/{id}', [IotController::class, 'ubahStatus']);
});