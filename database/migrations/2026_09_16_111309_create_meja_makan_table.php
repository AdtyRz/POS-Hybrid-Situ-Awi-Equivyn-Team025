<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TABEL 3 : meja_makan  (FR-002A, FR-003, FR-006 | PB-002)
 *
 * CATATAN REVISI v1.1:
 * - Kolom 'kode_meja' (bukan 'nomor_meja') menjadi nama kanonik agar seragam
 *   dengan DDL, ERD, dan seluruh diagram Sprint 1.
 * - 'qr_token' ditambahkan untuk generate QR Code e-Menu (FR-003).
 * - 'id_device' ditambahkan untuk binding MAC Address ESP32 Table Node (FR-006/FR-007).
 * - onDelete('restrict') dipilih agar riwayat pesanan & laporan FR-008 tidak
 *   hilang quando admin menghapus data meja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meja_makan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_meja', 10)->unique();
            $table->enum('area', ['Lesehan Bawah', 'Lesehan Atas', 'Kursi Atas']);
            $table->integer('kapasitas')->default(4);
            $table->string('qr_token', 32)->unique();
            $table->string('id_device', 20)->unique()->nullable();
            $table->enum('status_meja', ['kosong', 'terisi'])->default('kosong');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE meja_makan ADD CONSTRAINT meja_makan_kapasitas_positif CHECK (kapasitas > 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('meja_makan');
    }
};
