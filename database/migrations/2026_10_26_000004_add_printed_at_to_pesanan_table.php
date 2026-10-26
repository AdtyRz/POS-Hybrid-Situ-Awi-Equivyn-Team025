<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->timestamp('printed_at')->nullable()->after('alasan_void');
            $table->index('printed_at', 'idx_pesanan_printed_at');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropIndex('idx_pesanan_printed_at');
            $table->dropColumn('printed_at');
        });
    }
};
