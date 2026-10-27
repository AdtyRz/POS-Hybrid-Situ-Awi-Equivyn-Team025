<?php

namespace App\Support;

use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Aturan akses detail pesanan.
 *
 * Detail pesanan hanya boleh dibuka oleh staf aktif atau oleh tamu yang
 * membawa bukti sah untuk meja tersebut:
 * - session cookie hasil verifikasi kode saung, atau
 * - kode saung aktif, atau
 * - QR token meja.
 *
 * Pesanan juga wajib berada di dalam rentang sesi aktif. Tujuannya agar tamu
 * baru di meja yang sama tidak bisa membuka pesanan tamu sebelumnya hanya
 * karena mengetahui kode pesanan lama.
 */
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

        // aktifUntuk() juga menutup sesi kedaluwarsa dan membuat sesi baru
        // bila memang sudah tidak ada sesi aktif. Dengan begitu pesanan lama
        // otomatis tidak lolos pengecekan rentang waktu di bawah.
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

    public static function kodeSesiValid(Pesanan $pesanan, ?string $kodeSesi): bool
    {
        $kodeSesi = trim((string) $kodeSesi);

        if ($kodeSesi === '') {
            return false;
        }

        $sesi = self::sesiAktif($pesanan);

        return $sesi !== null
            && hash_equals((string) $sesi->kode_unik, $kodeSesi)
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
