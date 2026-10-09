<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IotApiController extends Controller
{
    public function panggilan(): JsonResponse
    {
        $antrean = LogPanggilPelayan::with('meja')
            ->whereIn('status_panggilan', ['menunggu', 'ditangani'])
            ->orderBy('waktu_panggil')
            ->get();

        return ApiResponse::success($antrean);
    }

    public function tangani(Request $request, $id): JsonResponse
    {
        $log = LogPanggilPelayan::find($id);
        if (! $log) {
            return ApiResponse::notFound('Panggilan tidak ditemukan');
        }

        $log->update([
            'status_panggilan' => 'ditangani',
            'waktu_selesai' => now(),
        ]);

        return ApiResponse::success($log->fresh());
    }
}
