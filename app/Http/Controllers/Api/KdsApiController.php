<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KdsApiController extends Controller
{
    public function antrean(): JsonResponse
    {
        $item = DetailPesanan::with(['menu.kategori', 'pesanan.meja'])
            ->whereHas('pesanan', function ($q) {
                $q->whereIn('status_pesanan', ['menunggu', 'diproses', 'siap']);
            })
            ->whereIn('status_item', ['menunggu', 'diproses', 'siap'])
            ->orderBy('pesanan_id')
            ->get();

        return ApiResponse::success($item);
    }
}
