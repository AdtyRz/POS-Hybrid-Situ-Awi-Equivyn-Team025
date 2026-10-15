<?php

namespace App\Support;

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
}
