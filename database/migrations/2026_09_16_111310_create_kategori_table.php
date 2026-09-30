<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABEL 2 : kategori  (FR-002, FR-004 | PB-002)
 *
 * FUNGSI: target_kds menjadi penentu routing item ke KDS Dapur atau KDS Bar
 * ketika pesanan lunas disiarkan ke KDS (FR-004).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 60)->unique();
            $table->enum('target_kds', ['dapur', 'bar']);
            $table->string('deskripsi', 191)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori');
    }
};
