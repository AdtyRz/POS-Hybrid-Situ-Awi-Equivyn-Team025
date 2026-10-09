<?php

namespace App\Http\Controllers;

use App\Http\Requests\PanggilPelayanRequest;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Pesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IotController extends Controller
{
    public function panggilPelayan(PanggilPelayanRequest $request): JsonResponse
    {
        $meja = MejaMakan::where('kode_meja', $request->kode_meja)
            ->where('qr_token', $request->qr_token)
            ->first();

        if ($meja === null) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Token QR tidak cocok dengan meja ini.',
            ], 401);
        }

        if ($palingBaru = $this->getPanggilanAktif($meja)) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Meja ini sudah punya panggilan yang belum selesai.',
                'data' => $palingBaru,
            ], 409);
        }

        $log = LogPanggilPelayan::create([
            'meja_id' => $meja->id,
            'status_panggilan' => 'menunggu',
            'waktu_panggil' => now(),
        ]);

        $log->refresh();

        return response()->json([
            'status' => 'sukses',
            'message' => 'Panggilan pelayan berhasil dikirim.',
            'data' => [
                'id' => $log->id,
                'kode_meja' => $meja->kode_meja,
                'waktu_panggil' => $log->waktu_panggil,
                'status_panggilan' => $log->status_panggilan,
            ],
        ], 201);
    }

    public function antrean(Request $request): JsonResponse
    {
        $antrean = LogPanggilPelayan::with('meja')
            ->whereIn('status_panggilan', ['menunggu', 'ditangani'])
            ->orderBy('waktu_panggil')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'kode_meja' => $log->meja->kode_meja,
                    'area' => $log->meja->area,
                    'waktu_panggil' => $log->waktu_panggil,
                    'status_panggilan' => $log->status_panggilan,
                ];
            });

        return response()->json([
            'status' => 'sukses',
            'total' => $antrean->count(),
            'data' => $antrean,
        ], 200);
    }

    public function ubahStatus(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'status_panggilan' => ['required', 'in:ditangani,selesai'],
        ], [
            'status_panggilan.required' => 'Status panggilan wajib diisi.',
            'status_panggilan.in' => 'Status panggilan harus ditangani atau selesai.',
        ]);

        $log = LogPanggilPelayan::findOrFail($id);

        $log->update([
            'status_panggilan' => $data['status_panggilan'],
            'waktu_selesai' => $data['status_panggilan'] === 'selesai' ? now() : null,
        ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'Status panggilan berhasil diubah.',
            'data' => $log->fresh(),
        ], 200);
    }

    public function statusMeja(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kode_meja' => ['required', 'string', 'exists:meja_makan,kode_meja'],
            'qr_token' => ['nullable', 'string'],
        ]);

        $meja = MejaMakan::where('kode_meja', $data['kode_meja'])->firstOrFail();

        if (! empty($data['qr_token']) && $meja->qr_token !== $data['qr_token']) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Token QR tidak cocok.',
            ], 401);
        }

        if ($meja->session_code && ! $meja->session_ended_at) {
            $pesananTerakhir = Pesanan::where('session_code', $meja->session_code)
                ->with(['detailPesanan.menu'])
                ->orderByDesc('id')
                ->first();

            if ($pesananTerakhir) {
                $items = $pesananTerakhir->detailPesanan->map(function ($d) {
                    return [
                        'nama_menu' => $d->menu->nama_menu ?? '-',
                        'jumlah' => $d->jumlah,
                    ];
                });

                return response()->json([
                    'status' => 'sukses',
                    'tipe' => 'terisi',
                    'data' => [
                        'meja' => $meja->kode_meja,
                        'session_code' => $meja->session_code,
                        'total_harga' => (float) $pesananTerakhir->total_bayar,
                        'status_pesanan' => $pesananTerakhir->status_pesanan,
                        'items' => $items,
                    ],
                ], 200);
            }
        }

        return response()->json([
            'status' => 'sukses',
            'tipe' => 'kosong',
            'data' => [
                'meja' => $meja->kode_meja,
            ],
        ], 200);
    }

    private function getPanggilanAktif(MejaMakan $meja)
    {
        return LogPanggilPelayan::where('meja_id', $meja->id)
            ->whereIn('status_panggilan', ['menunggu', 'ditangani'])
            ->orderByDesc('id')
            ->first();
    }
}
