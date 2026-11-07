<?php

namespace App\Http\Controllers\Api;

use App\Events\StatusPesananBerubah;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use App\Services\MidtransService;
use App\Services\StokService;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\PembayaranApiRequest;
use RuntimeException;

class PembayaranApiController extends Controller
{
    public function store(PembayaranApiRequest $request): JsonResponse
    {
        $data = $request->validated();

        $pesanan = Pesanan::where('kode_pesanan', $data['kode_pesanan'])->with('meja')->firstOrFail();

        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
            return ApiResponse::conflict('Pesanan sudah lunas');
        }

        if ($pesanan->status_pesanan === Pesanan::STATUS_DIBATALKAN) {
            return ApiResponse::conflict('Pesanan sudah dibatalkan');
        }

        $tagihan = (float) $pesanan->total_bayar;
        $dibayar = (float) $data['jumlah_bayar'];

        if ($dibayar < $tagihan) {
            return ApiResponse::validation(['jumlah_bayar' => ['Uang kurang']], 'Pembayaran gagal');
        }

        $midtrans = app(MidtransService::class);
        $statusMidtrans = null;

        if ($data['metode_pembayaran'] === 'qris') {

            $statusMidtrans = $midtrans->getTransactionStatus($pesanan->kode_pesanan);

            if (! $midtrans->verifikasiQris($pesanan, $statusMidtrans)) {
                return ApiResponse::validation(
                    ['metode_pembayaran' => ['Pembayaran QRIS belum terverifikasi oleh Midtrans.']],
                    'Pembayaran gagal'
                );
            }
        }

        $transactionId = filled(data_get($statusMidtrans, 'transaction_id'))
            ? trim((string) data_get($statusMidtrans, 'transaction_id'))
            : null;

        if ($data['metode_pembayaran'] === 'qris') {
            $dibayar = $tagihan;
        }

        try {
            DB::transaction(function () use ($pesanan, $data, $dibayar, $tagihan, $transactionId) {
                $terkunci = Pesanan::query()->whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

                if ($terkunci->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
                    throw new RuntimeException('Pesanan sudah lunas');
                }

                if ($terkunci->status_pesanan === Pesanan::STATUS_DIBATALKAN) {
                    throw new RuntimeException('Pesanan sudah dibatalkan');
                }

                app(StokService::class)->potongSetelahPembayaran($terkunci, auth()->id());

                Pembayaran::create([
                    'pesanan_id' => $terkunci->id,
                    'metode_pembayaran' => $data['metode_pembayaran'],
                    'status_pembayaran' => 'lunas',
                    'jumlah_bayar' => $dibayar,
                    'kembalian' => $data['metode_pembayaran'] === 'qris' ? 0 : $dibayar - $tagihan,
                    'transaction_id' => $data['metode_pembayaran'] === 'qris' ? $transactionId : null,
                    'order_id' => $data['metode_pembayaran'] === 'qris' ? $terkunci->kode_pesanan : null,
                ]);

                $terkunci->update([
                    'status_pesanan' => Pesanan::STATUS_DIPROSES,
                    'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
                    'metode_pembayaran' => $data['metode_pembayaran'],
                    'kasir_id' => auth()->id(),
                ]);
            });
        } catch (RuntimeException $exception) {
            return ApiResponse::validation(['kode_pesanan' => [$exception->getMessage()]], 'Pembayaran gagal');
        }

        $pesanan->refresh();

        Realtime::emit(new StatusPesananBerubah([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $pesanan->meja?->kode_meja,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
        ]));

        return ApiResponse::success($pesanan->fresh(), 'Pembayaran berhasil', 201)
            ->withHeaders(['Cache-Control' => 'no-store, private, max-age=0']);
    }
}
