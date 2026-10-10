<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\PesananApiRequest;
use App\Http\Requests\Api\UpdateStatusApiRequest;

class PesananApiController extends Controller
{
    public function store(PesananApiRequest $request, PesananService $pesananService): JsonResponse
    {
        $data = $request->validated();

        $meja = MejaMakan::findOrFail($data['meja_id']);

        if ($meja->status_meja !== 'kosong') {
            return ApiResponse::conflict('Meja sedang terisi');
        }

        // Validasi qr_token
        if (empty($data['qr_token']) || $data['qr_token'] !== $meja->qr_token) {
            return ApiResponse::validation(['qr_token' => ['QR token tidak valid atau tidak sesuai dengan meja ini.']], 'QR token tidak valid');
        }

        try {
            $pesanan = $pesananService->buat($meja, $data['jumlah'], $data['catatan'] ?? []);
        } catch (\RuntimeException $e) {
            return ApiResponse::validation(['jumlah' => [$e->getMessage()]], $e->getMessage());
        }

        return ApiResponse::success($pesanan->load(['detailPesanan', 'meja']), 'Pesanan berhasil dibuat', 201);
    }

    public function show(string $kode): JsonResponse
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan.menu', 'pembayaran'])
            ->where('kode_pesanan', $kode)
            ->first();

        if (! $pesanan) {
            return ApiResponse::notFound('Pesanan tidak ditemukan');
        }

        return ApiResponse::success($pesanan);
    }

    public function updateStatus(UpdateStatusApiRequest $request, string $kode): JsonResponse
    {
        $pesanan = Pesanan::where('kode_pesanan', $kode)->first();

        if (! $pesanan) {
            return ApiResponse::notFound('Pesanan tidak ditemukan');
        }

        $data = $request->validated();
        $pesanan->update(['status_pesanan' => $data['status_pesanan']]);

        return ApiResponse::success($pesanan->fresh());
    }
}
