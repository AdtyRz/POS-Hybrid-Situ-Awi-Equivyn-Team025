<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE pembayaran DROP CONSTRAINT IF EXISTS pembayaran_metode_pembayaran_check');
        DB::statement(
            "ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_metode_pembayaran_check "
            . "CHECK (metode_pembayaran::text = ANY (ARRAY['tunai'::text, 'qris'::text, 'transfer'::text]))"
        );
    }

    public function down(): void
    {
    }
};
