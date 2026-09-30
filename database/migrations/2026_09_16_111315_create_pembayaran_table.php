<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABEL 8 : pembayaran  (FR-005, FR-005A | PB-005)
 *
 * CATATAN REVISI v1.1:
 * 1) Kolom 'transaction_id' (dan order_id) DITAMBAHKAN. Pada versi lama kolom
 *    ini hilang sehingga bukti verifikasi webhook Midtrans tidak tersimpan
 *    dan FR-005 tidak dapat diaudit.
 * 2) 'pesanan_id' UNIQUE = satu pesanan memiliki tepat satu record pembayaran.
 *    Percobaan QRIS yang GAGAL tidak menginsert baris baru; webhook
 *    deny/expire cukup meng-update pesanan kembali ke status 'belum_bayar'.
 * 3) Metode pembayaran DIBATASI menjadi 'tunai' dan 'qris' saja. Metode
 *    'debit' dan 'kredit' yang muncul pada draft lama DIHAPUS karena berada
 *    di luar lingkup In-Scope Sprint 0 (lihat S0-05 Scope Definition).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->unique()->constrained('pesanan')->cascadeOnDelete();
            $table->enum('metode_pembayaran', ['tunai', 'qris']);
            $table->enum('status_pembayaran', [
                'menunggu', 'lunas', 'gagal', 'dibatalkan',
            ])->default('menunggu');
            $table->decimal('jumlah_bayar', 12, 2);
            $table->decimal('kembalian', 12, 2)->default(0);
            $table->string('transaction_id', 100)->unique()->nullable();
            $table->string('order_id', 100)->unique()->nullable();
            $table->timestamp('waktu_bayar')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
