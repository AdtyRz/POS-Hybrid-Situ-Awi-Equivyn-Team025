<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PesananApiRequest;
use App\Http\Requests\Api\UpdateStatusApiRequest;
use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\JsonResponse;

class PesananApiController extends Controller
{
    public function store(PesananApiRequest $request, PesananService $pesananService): JsonResponse
    {
        $data = $request->validated();

        $meja = MejaMakan::findOrFail($data['meja_id']);

        $qrToken = (string) ($meja->qr_token ?? '');

        if ($qrToken === '' || ! hash_equals($qrToken, (string) $data['qr_token'])) {
            return ApiResponse::validation(['qr_token' => ['QR token tidak valid atau tidak sesuai dengan meja ini.']], 'QR token tidak valid');
        }

        try {
            $pesanan = $pesananService->buat($meja, $data['jumlah'], $data['catatan'] ?? [], ['asal' => 'api']);
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

        if ($data['status_pesanan'] === Pesanan::STATUS_DIBATALKAN) {
            try {
                app(PesananService::class)->batalkan($pesanan, auth()->id(), 'Dibatalkan melalui API.');
            } catch (\RuntimeException $exception) {
                return ApiResponse::validation(['status_pesanan' => [$exception->getMessage()]], $exception->getMessage());
            }
        } else {
            $pesanan->update(['status_pesanan' => $data['status_pesanan']]);
        }

        $masihAdaPesananAktif = Pesanan::query()
            ->where('meja_id', $pesanan->meja_id)
            ->whereNotIn('status_pesanan', [Pesanan::STATUS_SELESAI, Pesanan::STATUS_DIBATALKAN])
            ->exists();

        if (! $masihAdaPesananAktif) {
            $pesanan->meja()->update(['status_meja' => 'kosong']);
        }

        return ApiResponse::success($pesanan->fresh());
    }
}
