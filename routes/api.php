<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\SesiMeja;

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

require __DIR__ . '/api_v1.php';

// Public endpoint untuk LCD/ESP32 - status meja + kode sesi aktif (tanpa auth)
Route::get('/iot/status-meja', function () {
    $meja = MejaMakan::select('id', 'kode_meja', 'area', 'kapasitas', 'status_meja')
        ->orderBy('kode_meja')
        ->get();

    $kodeSesi = SesiMeja::where('status', 'aktif')
        ->orderBy('id')
        ->get()
        ->keyBy('meja_id');

    $data = $meja->map(function ($m) use ($kodeSesi) {
        return [
            'id' => $m->id,
            'kode_meja' => $m->kode_meja,
            'area' => $m->area,
            'kapasitas' => $m->kapasitas,
            'status_meja' => $m->status_meja,
            'kode_sesi' => optional($kodeSesi->get($m->id))->kode_unik,
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'Berhasil mengambil status meja',
        'data' => $data,
    ]);
});

// Public endpoint untuk LCD/ESP32 - kode sesi aktif satu meja (berdasarkan id_device atau kode_meja).
Route::get('/iot/meja/{ident}', function (string $ident) {
    $meja = MejaMakan::where('id_device', $ident)
        ->orWhere('kode_meja', $ident)
        ->first();

    if (! $meja) {
        return response()->json(['success' => false, 'message' => 'Meja tidak ditemukan'], 404);
    }

    $sesi = SesiMeja::aktifUntuk($meja);

    return response()->json([
        'success' => true,
        'message' => 'Berhasil mengambil sesi meja',
        'data' => [
            'id' => $meja->id,
            'kode_meja' => $meja->kode_meja,
            'area' => $meja->area,
            'status_meja' => $meja->status_meja,
            'kode_sesi' => $sesi->kode_unik,
        ],
    ]);
});
