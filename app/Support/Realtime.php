<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;









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
                'kategori',
                'meja_makan',
                'pengaturan',
                'users',
                'riwayat_shift',
            ];

            $gabungan = collect($tabel)
                ->map(fn (string $nama) => "SELECT '{$nama}' AS nama, COUNT(*) AS jumlah, MAX(updated_at) AS terakhir FROM {$nama}")
                ->implode(' UNION ALL ');

            $sidik = [];

            foreach (DB::select($gabungan) as $baris) {
                $sidik[$baris->nama] = [
                    'jumlah' => (int) ($baris->jumlah ?? 0),
                    'terakhir' => (string) ($baris->terakhir ?? ''),
                ];
            }

            ksort($sidik);

            return hash('sha256', json_encode($sidik));
        });
    }
}
