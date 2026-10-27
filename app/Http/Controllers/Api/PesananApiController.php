<?php

namespace App\Http\Controllers\Api;

use App\Events\PesananBaru;
use App\Events\StatusPesananBerubah;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PesananApiRequest;
use App\Http\Requests\Api\UpdateStatusApiRequest;
use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Services\PesananService;
use App\Support\AksesPesanan;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        // Pesanan API selalu diikat ke sesi meja aktif. Sesi dibuat lebih dulu
        // agar pesanan tidak bisa dianggap milik sesi tamu sebelumnya.
        SesiMeja::aktifUntuk($meja);

        try {
            $pesanan = $pesananService->buat($meja, $data['jumlah'], $data['catatan'] ?? [], ['asal' => 'api']);
        } catch (\RuntimeException $e) {
            return ApiResponse::validation(['jumlah' => [$e->getMessage()]], $e->getMessage());
        }

        Realtime::emit(new PesananBaru([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $meja->kode_meja,
            'meja_id' => $meja->id,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
            'total_bayar' => (float) $pesanan->total_bayar,
        ]));

        return ApiResponse::success($pesanan->load(['detailPesanan', 'meja']), 'Pesanan berhasil dibuat', 201)
            ->withHeaders([
                'Cache-Control' => 'no-store, private, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ]);
    }

    public function show(Request $request, string $kode): JsonResponse
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan.menu', 'pembayaran'])
            ->where('kode_pesanan', $kode)
            ->first();

        if (! $pesanan) {
            return ApiResponse::notFound('Pesanan tidak ditemukan');
        }

        $qrToken = $request->query('qr_token')
            ?: $request->header('X-QR-Token')
            ?: $request->bearerToken();
        $kodeSaung = $request->query('kode_saung')
            ?: $request->header('X-Kode-Saung');

        $staf = AksesPesanan::staf($request->user());

        if (! $staf && blank($qrToken) && blank($kodeSaung)) {
            return ApiResponse::error('Akses pesanan memerlukan login staf, QR token meja, atau kode saung.', null, 401)
                ->withHeaders(['Cache-Control' => 'no-store, private, max-age=0']);
        }

        $boleh = $staf
            || AksesPesanan::qrTokenValid($pesanan, $qrToken)
            || AksesPesanan::kodeSesiValid($pesanan, $kodeSaung);

        if (! $boleh) {
            return ApiResponse::forbidden('Sesi meja sudah berakhir atau bukti akses tidak valid.')
                ->withHeaders(['Cache-Control' => 'no-store, private, max-age=0']);
        }

        $pesanan->makeHidden(['kasir_id', 'pelanggan_id', 'void_by', 'void_at', 'alasan_void']);

        return ApiResponse::success($pesanan)->withHeaders([
            'Cache-Control' => 'no-store, private, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
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

        $pesanan->refresh();

        Realtime::emit(new StatusPesananBerubah([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $pesanan->meja?->kode_meja,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
        ]));

        return ApiResponse::success($pesanan->fresh())
            ->withHeaders(['Cache-Control' => 'no-store, private, max-age=0']);
    }
}
