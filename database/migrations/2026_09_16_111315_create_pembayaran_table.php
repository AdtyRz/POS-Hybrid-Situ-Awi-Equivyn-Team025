<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
