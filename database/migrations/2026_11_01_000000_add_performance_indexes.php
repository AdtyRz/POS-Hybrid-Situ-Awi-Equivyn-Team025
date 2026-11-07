<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{




    public function up(): void
    {
        Schema::table('detail_pesanan', function (Blueprint $table) {
            $table->index('pesanan_id', 'idx_detail_pesanan_pesanan');
        });

        Schema::table('stok_mutasi', function (Blueprint $table) {
            $table->index('pesanan_id', 'idx_stok_mutasi_pesanan');
        });

        Schema::table('log_panggil_pelayan', function (Blueprint $table) {
            $table->index('meja_id', 'idx_log_panggil_meja');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->index('status_pembayaran', 'idx_pembayaran_status');
        });
    }

    public function down(): void
    {
        Schema::table('detail_pesanan', function (Blueprint $table) {
            $table->dropIndex('idx_detail_pesanan_pesanan');
        });

        Schema::table('stok_mutasi', function (Blueprint $table) {
            $table->dropIndex('idx_stok_mutasi_pesanan');
        });

        Schema::table('log_panggil_pelayan', function (Blueprint $table) {
            $table->dropIndex('idx_log_panggil_meja');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropIndex('idx_pembayaran_status');
        });
    }
};
