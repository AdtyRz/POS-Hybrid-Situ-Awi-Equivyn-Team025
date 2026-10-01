<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pesanan', 30)->unique();
            $table->foreignId('meja_id')->constrained('meja_makan')->restrictOnDelete();
            $table->foreignId('pelanggan_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kasir_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_bayar', 12, 2)->default(0);
            $table->enum('status_pesanan', [
                'menunggu', 'diproses', 'siap', 'diantar', 'selesai', 'dibatalkan',
            ])->default('menunggu');
            $table->enum('status_pembayaran', [
                'belum_bayar', 'sudah_bayar',
            ])->default('belum_bayar');
            $table->enum('metode_pembayaran', ['tunai', 'qris'])->nullable();
            $table->string('catatan', 255)->nullable();
            $table->timestamp('waktu_pesan')->useCurrent();
            $table->foreignId('void_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('void_at')->nullable();
            $table->string('alasan_void', 191)->nullable();
            $table->timestamps();

            $table->index(
                ['status_pesanan', 'status_pembayaran'],
                'idx_pesanan_status_antri'
            );

            $table->index(['meja_id', 'waktu_pesan'], 'idx_pesanan_meja_waktu');

            $table->index('waktu_pesan', 'idx_pesanan_waktu_pesan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
