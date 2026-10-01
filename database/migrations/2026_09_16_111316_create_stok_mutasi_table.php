<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_mutasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menu')->restrictOnDelete();
            $table->foreignId('pesanan_id')->nullable()->constrained('pesanan')->nullOnDelete();
            $table->enum('jenis', [
                'penjualan', 'pembatalan', 'penyesuaian',
            ]);
            $table->integer('jumlah_delta');
            $table->integer('stok_akhir');
            $table->string('keterangan', 191)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['menu_id', 'created_at'], 'idx_stok_mutasi_menu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_mutasi');
    }
};
