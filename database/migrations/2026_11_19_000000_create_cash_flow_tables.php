<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pembelian_bahan')) {
            Schema::create('pembelian_bahan', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('supplier', 100)->nullable();
                $table->string('keterangan', 255)->nullable();
                $table->decimal('total', 15, 2);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('tanggal', 'idx_pembelian_bahan_tanggal');
            });
        }

        if (! Schema::hasTable('pengeluaran')) {
            Schema::create('pengeluaran', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('kategori', 40);
                $table->string('keterangan', 255);
                $table->decimal('jumlah', 15, 2);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('tanggal', 'idx_pengeluaran_tanggal');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
        Schema::dropIfExists('pembelian_bahan');
    }
};
