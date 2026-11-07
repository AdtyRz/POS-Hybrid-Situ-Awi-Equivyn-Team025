<?php

namespace App\Support;

use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Models\User;
use Illuminate\Http\Request;

















class AksesPesanan
{
    public const STAFF_ROLES = ['admin', 'kasir', 'pelayan', 'koki', 'barista'];

    public static function staf(?User $user): bool
    {
        return $user !== null
            && $user->is_aktif
            && in_array($user->role, self::STAFF_ROLES, true);
    }

    public static function sesiAktif(Pesanan $pesanan): ?SesiMeja
    {
        $meja = $pesanan->meja;

        if (! $meja) {
            return null;
        }




        return SesiMeja::aktifUntuk($meja);
    }

    public static function dalamSesi(Pesanan $pesanan, SesiMeja $sesi): bool
    {
        if ((int) $pesanan->meja_id !== (int) $sesi->meja_id) {
            return false;
        }

        if ($pesanan->waktu_pesan === null || $sesi->dibuka_pada === null) {
            return false;
        }

        return $pesanan->waktu_pesan->greaterThanOrEqualTo($sesi->dibuka_pada);
    }

    public static function sesiCookieValid(Request $request, Pesanan $pesanan): bool
    {
        $sesiId = $request->session()->get('sesi_meja_' . $pesanan->meja_id);

        if ($sesiId === null) {
            return false;
        }

        $sesi = self::sesiAktif($pesanan);

        return $sesi !== null
            && (int) $sesiId === (int) $sesi->id
            && self::dalamSesi($pesanan, $sesi);
    }

    public static function qrTokenValid(Pesanan $pesanan, ?string $qrToken): bool
    {
        $qrToken = trim((string) $qrToken);
        $tokenMeja = trim((string) ($pesanan->meja?->qr_token ?? ''));

        if ($qrToken === '' || $tokenMeja === '' || ! hash_equals($tokenMeja, $qrToken)) {
            return false;
        }

        $sesi = self::sesiAktif($pesanan);

        return $sesi !== null
            && self::dalamSesi($pesanan, $sesi);
    }
}
