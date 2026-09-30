<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABEL 9 : stok_mutasi  (FR-002, FR-005B | PB-002)
 *
 * TABEL BARU pada revisi v1.1. Tabel ini menutup celah besar pada draft lama:
 * sebelumnya perubahan stok akibat penjualan TIDAK pernah tercatat, sehingga
 * tidak ada jejak audit sehingga Admin tidak dapat merekonstruksi selisih stok.
 *
 * DAFTAR MUTASI:
 *  - penjualan     : jumlah_delta < 0 (auto-cut saat pesanan dibayar)
 *  - pembatalan    : jumlah_delta > 0 (stok dikembalikan saat VOID FR-005B)
 *  - penyesuaian   : jumlah_delta positif/negatif (koreksi stok manual oleh Admin)
 *
 * 'stok_akhir' disimpan sebagai snapshot hasil sehingga laporan stok dapat
 * diverifikasi ulang tanpa menghitung ulang seluruh riwayat mutasi.
 */
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
