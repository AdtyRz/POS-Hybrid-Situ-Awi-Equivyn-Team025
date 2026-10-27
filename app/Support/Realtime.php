<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pembungkus broadcast yang aman.
 *
 * Broadcast WebSocket (Reverb) tidak boleh mematahkan alur bisnis inti.
 * Bila server Reverb sedang mati/tidak dapat dihubungi, kegagalan broadcast
 * cukup dicatat sebagai warning — data pesanan tetap tersimpan dan halaman
 * yang tidak menerima event akan tetap menyegarkan diri lewat fallback polling.
 */
class Realtime
{
    public static function emit(object $event): void
    {
        try {
            event($event);
        } catch (\Throwable $e) {
            Log::warning('Broadcast realtime gagal: ' . $e->getMessage());
        }
    }

    /**
     * Sidik jari data yang berubah. Halaman memakai nilai ini untuk fallback
     * polling bila WebSocket/Reverb tidak tersedia.
     */
    public static function version(): string
    {
        return Cache::remember('realtime-version', 2, function () {
            $tabel = [
                'pesanan',
                'detail_pesanan',
                'pembayaran',
                'log_panggil_pelayan',
                'sesi_meja',
                'menu',
                'meja_makan',
                'users',
            ];

            $sidik = [];

            foreach ($tabel as $nama) {
                $baris = DB::table($nama)
                    ->selectRaw('COUNT(*) as jumlah, MAX(updated_at) as terakhir')
                    ->first();

                $sidik[$nama] = [
                    'jumlah' => (int) ($baris->jumlah ?? 0),
                    'terakhir' => $baris->terakhir ?? null,
                ];
            }

            return hash('sha256', json_encode($sidik));
        });
    }
}
