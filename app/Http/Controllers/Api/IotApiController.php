<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Events\PanggilanPelayan;
use App\Http\Controllers\Controller;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Pengaturan;
use App\Models\SesiMeja;
use App\Services\IotStatusService;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IotApiController extends Controller
{
    public function statusMeja(IotStatusService $service): JsonResponse
    {
        return response()
            ->json($service->statusSemuaMeja())
            ->header('Cache-Control', 'no-store, private, max-age=0');
    }

    public function statusSatuMeja(string $ident, IotStatusService $service): JsonResponse
    {
        $data = $service->detailMeja($ident);

        if ($data === null) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak ditemukan',
            ], 404);
        }

        return response()
            ->json($data)
            ->header('Cache-Control', 'no-store, private, max-age=0');
    }

    /**
     * Panggilan pelayan dari perangkat IoT. Perangkat wajib mengirim kode sesi
     * yang sedang tampil di LCD. Kode sesi lama otomatis ditolak.
     */
    public function panggilPelayan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kode_meja' => ['required', 'string', 'max:50'],
            'kode_sesi' => ['required', 'string', 'max:10'],
        ]);

        $meja = MejaMakan::where('kode_meja', $data['kode_meja'])->first();

        if (! $meja) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Meja tidak ditemukan',
            ], 404);
        }

        $sesi = SesiMeja::query()
            ->where('meja_id', $meja->id)
            ->where('status', 'aktif')
            ->latest('id')
            ->first();

        if (! $sesi || ! hash_equals((string) $sesi->kode_unik, trim($data['kode_sesi']))) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Kode sesi tidak valid. Ambil kode terbaru dari layar meja.',
            ], 403);
        }

        $cooldown = max(1, (int) Pengaturan::nilai('anti_spam_cooldown', 5));
        $panggilanBaru = LogPanggilPelayan::query()
            ->where('meja_id', $meja->id)
            ->whereIn('status_panggilan', ['menunggu', 'ditangani'])
            ->where('waktu_panggil', '>=', now()->subSeconds($cooldown))
            ->exists();

        if ($panggilanBaru) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Panggilan meja ini masih dalam jeda anti-spam.',
            ], 429);
        }

        $log = LogPanggilPelayan::create([
            'meja_id' => $meja->id,
            'status_panggilan' => 'menunggu',
        ]);

        Realtime::emit(new PanggilanPelayan([
            'id' => $log->id,
            'meja_id' => $meja->id,
            'meja' => $meja->kode_meja,
            'area' => $meja->area,
            'status_panggilan' => 'menunggu',
        ]));

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Panggilan dari meja berhasil dikirim ke server.',
            'data' => $log,
        ], 200);
    }

    public function panggilan(): JsonResponse
    {
        $antrean = LogPanggilPelayan::with('meja')
            ->whereIn('status_panggilan', ['menunggu', 'ditangani'])
            ->orderBy('waktu_panggil')
            ->get();

        return ApiResponse::success($antrean);
    }

    public function tangani($id): JsonResponse
    {
        $log = LogPanggilPelayan::find($id);
        if (! $log) {
            return ApiResponse::notFound('Panggilan tidak ditemukan');
        }

        $log->update([
            'status_panggilan' => 'ditangani',
            'waktu_selesai' => now(),
        ]);

        return ApiResponse::success($log->fresh());
    }
}
