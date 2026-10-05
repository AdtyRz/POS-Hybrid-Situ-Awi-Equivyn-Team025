<?php

namespace App\Http\Controllers;

use App\Http\Requests\PanggilPelayanRequest;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
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

    public function ubahStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status_panggilan' => ['required', 'in:ditangani,selesai'],
        ]);

        $log = LogPanggilPelayan::findOrFail($id);

        $log->update([
            'status_panggilan' => $request->status_panggilan,
            'pelayan_id' => $request->user()?->id,
            'waktu_selesai' => $request->status_panggilan === 'selesai' ? now() : null,
        ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'Status panggilan diperbarui.',
            'data' => [
                'id' => $log->id,
                'status_panggilan' => $log->status_panggilan,
                'waktu_selesai' => $log->waktu_selesai,
            ],
        ], 200);
    }

    private function getPanggilanAktif(MejaMakan $meja): ?LogPanggilPelayan
    {
        return $meja->logPanggil()
            ->whereIn('status_panggilan', ['menunggu', 'ditangani'])
            ->orderByDesc('waktu_panggil')
            ->first();
    }
}