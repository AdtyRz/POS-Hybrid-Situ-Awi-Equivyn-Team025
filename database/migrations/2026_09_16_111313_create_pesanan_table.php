<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABEL 6 : pesanan  (FR-003, FR-005, FR-005A, FR-005B | PB-003, PB-005)
 *
 * CATATAN REVISI v1.1 - INI ADALAH PERBAIKAN PALING BESAR:
 *
 * 1) 'kasir_id' DITAMBAHKAN. Pada versi lama kolom ini hilang sehingga tidak
 *    ada jejak audit siapa yang memvalidasi pembayaran (FR-005A).
 *
 * 2) 'status_pesanan' MEMILIKI 6 NILAI BAHASA INDONESIA. Versi lama hanya
 *    memuat 5 dan TIDAK memuat 'diantar', padahal alur "Pelayan mengantar
 *    -> diantar" wajib ada di seluruh diagram Sprint 1.
 *    KANONIK: menunggu | diproses | siap | diantar | selesai | dibatalkan
 *
 * 3) Kolom void_by / void_at / alasan_void DITAMBAHKAN untuk memenuhi
 *    Acceptance Criteria US-003 "sistem mencatat log riwayat void" (FR-005B).
 *
 * 4) 'pelanggan_id' nullable karena pesanan dapat dibuat oleh Kasir di meja
 *    (walk-in) maupun oleh tamu melalui QR Code. nullOnDelete menjaga
 *    laporan penjualan tetap utuh meskipun data tamu dihapus.
 */
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

            // Antrian KDS: ORDER BY status_pesanan, waktu_pesan
            $table->index(
                ['status_pesanan', 'status_pembayaran'],
                'idx_pesanan_status_antri'
            );
            // Live tracking status pesanan di LCD meja (FR-007)
            $table->index(['meja_id', 'waktu_pesan'], 'idx_pesanan_meja_waktu');
            // Laporan penjualan harian/mingguan/bulanan (FR-008)
            $table->index('waktu_pesan', 'idx_pesanan_waktu_pesan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
