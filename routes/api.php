<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;

Route::post('/iot/panggil-pelayan', function (Request $request) {
    $request->validate([
        'kode_meja' => 'required|string',
    ]);

    $meja = MejaMakan::where('kode_meja', $request->kode_meja)->first();

    if (! $meja) {
        return response()->json(['status' => 'error', 'message' => 'Meja tidak ditemukan'], 404);
    }

    $log = LogPanggilPelayan::create([
        'meja_id' => $meja->id,
        'status_panggilan' => 'menunggu',
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Panggilan dari meja berhasil dikirim ke server.',
        'data' => $log
    ], 200);
});
