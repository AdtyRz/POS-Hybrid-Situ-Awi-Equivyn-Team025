<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABEL 5 : log_panggil_pelayan  (FR-006, US-002 | PB-006)
 *
 * CATATAN REVISI v1.1 - PERBAIKAN PALING PENTING PADA DOKUMEN INI:
 * Nama tabel pada draft S1-01 adalah "call_waiter_logs" (campur bahasa Inggris).
 * NAMA KANONIK adalah "log_panggil_pelayan" agar seragam dengan seluruh
 * dokumen berbahasa Indonesia. Seluruh diagram & User Story WAJIB memakai
 * nama ini tanpa kecuali.
 *
 * Kolom 'ditangani_oleh' pada versi lama DINAMAIKAN menjadi 'pelayan_id'
 * dan tetap berupa foreignId sehingga referensialitasnya terjaga.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_panggil_pelayan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meja_id')->constrained('meja_makan')->restrictOnDelete();
            $table->foreignId('pelayan_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_panggilan', ['menunggu', 'ditangani', 'selesai'])
                ->default('menunggu');
            $table->timestamp('waktu_panggil')->useCurrent();
            $table->timestamp('waktu_selesai')->nullable();
            $table->timestamps();

            // Antrean alert panggil pelayan di Mobile POS (FR-006)
            $table->index(
                ['status_panggilan', 'waktu_panggil'],
                'idx_log_panggil_antrian'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_panggil_pelayan');
    }
};
