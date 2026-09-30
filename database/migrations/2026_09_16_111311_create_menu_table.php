<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABEL 4 : menu  (FR-002, FR-003 | PB-002)
 *
 * CATATAN REVISI v1.1:
 * - Kolom 'stok_menu' dipertahankan (wajib untuk FR-002 "kelola stok" dan
 *   skenario US-001 "menu stoknya 0 -> tombol disabled + badge Stok Habis").
 * - Kolom 'status_tersedia' DIHAPUS. Ketersediaan kini diturunkan secara
 *   deterministik dari (stok_menu > 0), menghilangkan risiko anomali update
 *   ganda antara kolom stok dan kolom status.
 * - 'slug' ditambahkan untuk routing URL e-Menu yang ramah mesin pencari.
 * - onDelete('restrict') agar kategori tidak dapat dihapus masih dipakai menu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $table->string('nama_menu', 150);
            $table->string('slug', 160)->unique();
            $table->text('deskripsi')->nullable();
            $table->string('foto_menu', 191)->nullable();
            $table->decimal('harga', 12, 2);
            $table->integer('stok_menu')->default(0);
            $table->timestamps();

            // Partial index: pencarian cepat menu "Stok Habis"
            $table->index('id', 'idx_menu_stok_habis')->where('stok_menu = 0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
