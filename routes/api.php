<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthCheckController;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;

Route::post('/iot/panggil-pelayan', function (Request $request) {
    $request->validate([
        'nomor_meja' => 'required',
        'jenis_panggilan' => 'required|in:service,bill'
    ]);

    $meja = MejaMakan::where('nomor_meja', $request->nomor_meja)->first();

    if (!$meja) {
        return response()->json(['status' => 'error', 'message' => 'Meja tidak ditemukan'], 404);
    }

    $log = LogPanggilPelayan::create([
        'id_meja' => $meja->id,
        'jenis' => $request->jenis_panggilan,
        'status' => 'pending' 
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Panggilan dari meja berhasil dikirim ke server.',
        'data' => $log
    ], 200);
});

Route::get('/health', [HealthCheckController::class, 'index']);
