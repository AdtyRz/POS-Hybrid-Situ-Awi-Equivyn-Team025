<?php

namespace App\Services;

use App\Models\Pengaturan;
use App\Models\Pesanan;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);
    }






    public function createQrisCharge(Pesanan $pesanan, bool $renewIfExpired = false): ?array
    {
        try {
            return Cache::lock('midtrans-qris:' . $pesanan->kode_pesanan, 30)
                ->block(8, fn () => $this->createQrisChargeTerkunci($pesanan));
        } catch (LockTimeoutException $e) {
            return null;
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    private function createQrisChargeTerkunci(Pesanan $pesanan): ?array
    {
        try {
            $status = $this->getTransactionStatus($pesanan->kode_pesanan);
            $trx = strtolower($status->transaction_status ?? '');


            if ($trx === 'pending') {
                $qrUrl = $status ? $this->extractQrUrl($status->actions ?? []) : null;


                if (! $qrUrl) {
                    $qrUrl = $this->buildQrisUrl($status->transaction_id ?? null);
                }

                return [
                    'order_id' => $pesanan->kode_pesanan,
                    'transaction_id' => $status->transaction_id ?? null,
                    'qr_url' => $qrUrl,
                    'transaction_status' => 'pending',
                    'expiry_time' => $status->expiry_time ?? null,
                ];
            }


            if (in_array($trx, ['settlement', 'capture'], true)) {
                return [
                    'order_id' => $pesanan->kode_pesanan,
                    'transaction_id' => $status->transaction_id ?? null,
                    'qr_url' => null,
                    'transaction_status' => $trx,
                    'expiry_time' => $status->expiry_time ?? null,
                ];
            }



            $params = [
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id' => $pesanan->kode_pesanan,
                    'gross_amount' => (int) round((float) $pesanan->total_bayar),
                ],
                'qris' => [
                    'acquirer' => 'gopay',
                ],
                'customer_details' => [
                    'first_name' => $pesanan->meja->kode_meja ?? 'Tamu',
                ],
                'expiry' => [
                    'unit' => 'minutes',
                    'duration' => $this->qrisExpiryMinutes(),
                ],
            ];

            $response = CoreApi::charge($params);

            $qrUrl = $this->extractQrUrl($response->actions ?? [])
                ?: $this->buildQrisUrl($response->transaction_id ?? null);

            return [
                'order_id' => $pesanan->kode_pesanan,
                'transaction_id' => $response->transaction_id ?? null,
                'qr_url' => $qrUrl,
                'transaction_status' => $response->transaction_status ?? 'pending',
                'expiry_time' => $response->expiry_time ?? null,
            ];
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function qrisExpiryMinutes(): int
    {
        return max(5, min(60, (int) Pengaturan::nilai('qris_expiry_minutes', 5)));
    }

    private function buildQrisUrl(?string $transactionId): ?string
    {
        if (empty($transactionId)) {
            return null;
        }

        $base = config('midtrans.is_production', false)
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        return $base . '/v2/qris/' . $transactionId . '/qr-code';
    }

    private function extractQrUrl(array $actions): ?string
    {
        foreach ($actions as $action) {
            $name = is_array($action) ? ($action['name'] ?? null) : ($action->name ?? null);
            $url = is_array($action) ? ($action['url'] ?? null) : ($action->url ?? null);

            if ($name === 'generate-qr-code' && $url) {
                return $url;
            }
        }

        return null;
    }

    public function verifySignature(array $payload): bool
    {
        try {
            $serverKey = config('midtrans.server_key');
            if (empty($serverKey)) {
                return false;
            }

            $orderId = $payload['order_id'] ?? null;
            $statusCode = $payload['status_code'] ?? null;
            $grossAmount = $payload['gross_amount'] ?? null;
            $signature = $payload['signature_key'] ?? null;

            if (!$orderId || !$statusCode || !$grossAmount || !$signature) {
                return false;
            }

            $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            return hash_equals($expected, $signature);
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function getTransactionStatus(string $orderId)
    {
        try {
            return Transaction::status($orderId);
        } catch (\Exception $e) {

            if ((int) $e->getCode() !== 404) {
                report($e);
            }
            return null;
        }
    }






    public function verifikasiQris(Pesanan $pesanan, mixed $status = null): bool
    {
        $status ??= $this->getTransactionStatus($pesanan->kode_pesanan);

        if (! is_object($status) && ! is_array($status)) {
            return false;
        }

        $data = (array) $status;
        $transactionStatus = strtolower((string) ($data['transaction_status'] ?? ''));
        $fraudStatus = strtolower((string) ($data['fraud_status'] ?? ''));

        $lunas = $transactionStatus === 'settlement'
            || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

        if (! $lunas) {
            return false;
        }

        $orderId = (string) ($data['order_id'] ?? '');
        $grossAmount = $data['gross_amount'] ?? null;

        if ($orderId === ''
            || ! hash_equals((string) $pesanan->kode_pesanan, $orderId)
            || $grossAmount === null
            || $grossAmount === '') {
            return false;
        }

        return abs((float) $grossAmount - (float) $pesanan->total_bayar) < 0.01;
    }
}
