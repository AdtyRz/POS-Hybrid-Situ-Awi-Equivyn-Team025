<?php

namespace App\Http\Controllers\Api;

use App\Events\StatusPesananBerubah;
use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\MidtransService;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MidtransNotificationController extends Controller
{
    public function __construct(protected MidtransService $midtransService) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (! $this->midtransService->verifySignature($payload)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = strtolower($payload['transaction_status'] ?? '');
        $fraudStatus = strtolower($payload['fraud_status'] ?? '');
        $transactionId = $payload['transaction_id'] ?? null;

        if (! $orderId) {
            return response()->json(['status' => 'error', 'message' => 'Invalid order_id'], 400);
        }

        $pesanan = Pesanan::where('kode_pesanan', $orderId)->first();

        if (! $pesanan) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        // If already paid or cancelled, just respond OK without changing anything.
        if (
            $pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR
            || $pesanan->status_pesanan === Pesanan::STATUS_DIBATALKAN
        ) {
            return response()->json(['status' => 'ok', 'message' => 'No action needed']);
        }

        DB::transaction(function () use ($pesanan, $transactionStatus, $fraudStatus, $transactionId) {
            // Payment success conditions
            $isSuccess = false;
            if ($transactionStatus === 'capture') {
                $isSuccess = ($fraudStatus === 'accept');
            } elseif ($transactionStatus === 'settlement') {
                $isSuccess = true;
            }

            if ($isSuccess) {
                // Set as paid and move to kitchen
                $pesanan->update([
                    'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
                    'status_pesanan' => Pesanan::STATUS_DIPROSES,
                    'metode_pembayaran' => 'qris',
                ]);

                // Create payment record if not exists (minimal)
                if (! $pesanan->pembayaran) {
                    $pesanan->pembayaran()->create([
                        'metode_pembayaran' => 'qris',
                        'status_pembayaran' => 'lunas',
                        'jumlah_bayar' => (float) $pesanan->total_bayar,
                        'kembalian' => 0,
                        'transaction_id' => $transactionId,
                        'order_id' => $orderId,
                    ]);
                }
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'])) {
                // On failure, don't change meja status immediately - let business logic decide
                // Just ensure we don't mark as paid
                $pesanan->update([
                    'status_pembayaran' => $pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR
                        ? Pesanan::PAYMENT_SUDAH_BAYAR
                        : Pesanan::PAYMENT_BELUM_BAYAR,
                ]);
            }
        });

        $pesanan->refresh();
        Realtime::emit(new StatusPesananBerubah([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $pesanan->meja->kode_meja ?? null,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
        ]));

        return response()->json(['status' => 'ok']);
    }
}
