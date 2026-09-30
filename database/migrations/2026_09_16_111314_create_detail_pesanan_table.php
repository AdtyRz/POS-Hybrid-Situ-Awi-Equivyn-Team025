<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABEL 7 : detail_pesanan  (FR-003, FR-004 | PB-003, PB-004)
 *
 * CATATAN REVISI v1.1:
 * - Kolom 'harga_satuan' DAN 'subtotal' DITAMBAHKAN. Pada versi lama subtotal
 *   tidak pernah tersimpan sehingga rekap totalBayar tidak dapat diverifikasi
 *   terhadap rincian item (terjadi risiko selisih angka pada struk/laporan).
 *
 * - 'harga_satuan' adalah SNAPSHOT HARGA saat transaksi dibuat. Dismpan
 *   sengaja walaupun secara teori turunan dari menu.harga, karena harga menu
 *   dapat berubah di masa depan sedangkan struk thermal dan laporan omzet
 *   FR-005/FR-008 WAJIB mencerminkan harga historis. Ini denormalisasi yang
 *   JUSTIFIED (diperbolehkan), bukan pelanggaran 3NF.
 *
 * - 'status_item' 4 nilai mengikuti siklus item KDS, terpisah dari
 *   status_pesanan di tabel pesanan.
 *
 * - onDelete('cascade') dari pesanan: detail tidak pernah hidup tanpa induk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menu')->restrictOnDelete();
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->string('catatan', 255)->nullable();
            $table->enum('status_item', [
                'menunggu', 'diproses', 'siap', 'diantar',
            ])->default('menunggu');
            $table->timestamps();

            $table->index('status_item', 'idx_detail_status_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
