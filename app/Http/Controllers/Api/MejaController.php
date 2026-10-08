<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\MejaMakan;
use Illuminate\Http\JsonResponse;

class MejaController extends Controller
{
    public function index(): JsonResponse
    {
        $meja = MejaMakan::orderBy('kode_meja')->get();

        return ApiResponse::success($meja, 'Berhasil mengambil daftar meja', 200);
    }
}
