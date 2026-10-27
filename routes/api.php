<?php

use App\Http\Controllers\Api\IotApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Endpoint perangkat IoT
|--------------------------------------------------------------------------
|
| Semua endpoint di bawah ini hanya untuk LCD/ESP32. Token dikirim lewat
| header X-IoT-Token dan tidak pernah ditaruh di URL.
|
*/
Route::middleware('iot.token')->prefix('iot')->group(function () {
    Route::post('/panggil-pelayan', [IotApiController::class, 'panggilPelayan'])
        ->middleware('throttle:10,1');

    Route::get('/status-meja', [IotApiController::class, 'statusMeja'])
        ->middleware('throttle:120,1');

    Route::get('/meja/{ident}', [IotApiController::class, 'statusSatuMeja'])
        ->middleware('throttle:120,1');
});

require __DIR__.'/api_v1.php';
