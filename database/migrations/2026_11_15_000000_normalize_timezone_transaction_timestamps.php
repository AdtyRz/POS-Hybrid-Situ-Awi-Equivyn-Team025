<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Selaraskan data lama yang terisi CURRENT_TIMESTAMP dengan timezone
     * sesi PostgreSQL (Asia/Jakarta) sehingga tampak 7 jam lebih maju.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ([
            ['pesanan', 'waktu_pesan'],
            ['pembayaran', 'waktu_bayar'],
            ['log_panggil_pelayan', 'waktu_panggil'],
        ] as [$tabel, $kolom]) {
            DB::table($tabel)
                ->whereRaw("{$kolom} - created_at between interval '6 hours' and interval '8 hours'")
                ->update([$kolom => DB::raw("{$kolom} - interval '7 hours'")]);
        }
    }

    public function down(): void
    {
        // Data historis yang sudah dinormalisasi tidak dikembalikan agar tidak bergeser lagi.
    }
};
