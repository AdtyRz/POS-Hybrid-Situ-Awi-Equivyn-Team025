<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PembayaranApiRequest;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($pesanan, $data, $dibayar, $tagihan) {
            Pembayaran::create([
                'pesanan_id' => $pesanan->id,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'status_pembayaran' => 'lunas',
                'jumlah_bayar' => $dibayar,
                'kembalian' => $dibayar - $tagihan,
            ]);

            $pesanan->update([
                'status_pesanan' => Pesanan::STATUS_DIPROSES,
                'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
                'metode_pembayaran' => $data['metode_pembayaran'],
            ]);
        });

        return ApiResponse::success($pesanan->fresh(), 'Pembayaran berhasil', 201);
    }
}
