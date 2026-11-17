<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data lama dapat memiliki metode pembayaran transfer. Selaraskan
     * constraint PostgreSQL agar laporan dapat memfilter metode tersebut.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE pesanan DROP CONSTRAINT IF EXISTS pesanan_metode_pembayaran_check');
        DB::statement(
            "ALTER TABLE pesanan ADD CONSTRAINT pesanan_metode_pembayaran_check "
            . "CHECK (metode_pembayaran::text = ANY (ARRAY['tunai'::text, 'qris'::text, 'transfer'::text]))"
        );
    }

    public function down(): void
    {
        // Constraint tidak dikembalikan agar data transfer lama tetap valid.
    }
};
