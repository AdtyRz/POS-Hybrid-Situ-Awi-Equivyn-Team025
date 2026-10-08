<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PesananApiController extends Controller
{
    public function store(Request $request, PesananService $pesananService): JsonResponse
    {
        $data = $request->validate([
            'meja_id' => ['required', 'integer', 'exists:meja_makan,id'],
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['required', 'integer', 'min:1', 'max:99'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:255'],
        ]);

        $meja = MejaMakan::findOrFail($data['meja_id']);

        if ($meja->status_meja !== 'kosong') {
            return ApiResponse::conflict('Meja sedang terisi');
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

    public function updateStatus(Request $request, string $kode): JsonResponse
    {
        $pesanan = Pesanan::where('kode_pesanan', $kode)->first();

        if (! $pesanan) {
            return ApiResponse::notFound('Pesanan tidak ditemukan');
        }

        $data = $request->validate([
            'status_pesanan' => ['required', 'in:diproses,siap,diantar'],
        ]);

        $pesanan->update(['status_pesanan' => $data['status_pesanan']]);

        return ApiResponse::success($pesanan->fresh());
    }
}
