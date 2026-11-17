<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simpan batas waktu QRIS per pesanan agar hitung mundur tamu dan
     * pembatalan otomatis memakai waktu kedaluwarsa yang sama.
     */
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->timestamp('qris_expires_at')->nullable()->index('idx_pesanan_qris_expires');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropIndex('idx_pesanan_qris_expires');
            $table->dropColumn('qris_expires_at');
        });
    }
};
