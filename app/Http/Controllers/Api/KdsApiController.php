<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KdsApiController extends Controller
{
    public function antrean(Request $request): JsonResponse
    {
        $role = (string) $request->user()?->role;
        $targetRole = match ($role) {
            'koki' => 'dapur',
            'barista' => 'bar',
            default => null,
        };

        if ($targetRole === null) {
            return ApiResponse::forbidden('Akun ini tidak memiliki akses ke stasiun KDS.');
        }

        $targetDiminta = $request->query('target');
        $target = blank($targetDiminta)
            ? $targetRole
            : strtolower(trim((string) $targetDiminta));

        if (! in_array($target, ['dapur', 'bar'], true)) {
            return ApiResponse::validation([
                'target' => ['Target KDS harus dapur atau bar.'],
            ], 'Target KDS tidak valid');
        }

        if ($target !== $targetRole) {
            return ApiResponse::forbidden('Anda hanya boleh mengakses antrean stasiun sendiri.');
        }

        $item = DetailPesanan::with(['menu.kategori', 'pesanan.meja'])
            ->whereHas('menu.kategori', function ($q) use ($target) {
                $q->where('target_kds', $target);
            })
            ->whereHas('pesanan', function ($q) {
                $q->whereIn('status_pesanan', ['menunggu', 'diproses', 'siap']);
            })
            ->whereIn('status_item', ['menunggu', 'diproses', 'siap'])
            ->orderBy('pesanan_id')
            ->get();

        $response = ApiResponse::success($item);
        $data = $response->getData(true);
        $data['target'] = $target;
        $response->setData($data);

        return $response
            ->header('X-KDS-Target', $target)
            ->header('Cache-Control', 'no-store, private, max-age=0');
    }
}
