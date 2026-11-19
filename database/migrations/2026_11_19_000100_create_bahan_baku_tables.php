<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bahan_baku')) {
            Schema::create('bahan_baku', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 120)->unique();
                $table->string('satuan', 20)->default('pcs');
                $table->decimal('stok', 14, 3)->default(0);
                $table->decimal('stok_minimum', 14, 3)->default(0);
                $table->decimal('harga_rata_rata', 15, 2)->default(0);
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();

                $table->index('nama', 'idx_bahan_baku_nama');
            });
        }

        if (! Schema::hasTable('pembelian_bahan_detail')) {
            Schema::create('pembelian_bahan_detail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pembelian_bahan_id')->constrained('pembelian_bahan')->cascadeOnDelete();
                $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->restrictOnDelete();
                $table->decimal('jumlah', 14, 3);
                $table->decimal('harga_satuan', 15, 2);
                $table->decimal('subtotal', 15, 2);
                $table->timestamps();

                $table->index('pembelian_bahan_id', 'idx_pembelian_bahan_detail_header');
                $table->index('bahan_baku_id', 'idx_pembelian_bahan_detail_bahan');
            });
        }

        if (! Schema::hasTable('resep_menu')) {
            Schema::create('resep_menu', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_id')->constrained('menu')->cascadeOnDelete();
                $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->restrictOnDelete();
                $table->decimal('jumlah', 14, 3);
                $table->timestamps();

                $table->unique(['menu_id', 'bahan_baku_id'], 'resep_menu_unik');
            });
        }

        if (! Schema::hasTable('bahan_stok_mutasi')) {
            Schema::create('bahan_stok_mutasi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->cascadeOnDelete();
                $table->foreignId('pesanan_id')->nullable()->constrained('pesanan')->nullOnDelete();
                $table->foreignId('pembelian_bahan_id')->nullable()->constrained('pembelian_bahan')->cascadeOnDelete();
                $table->string('jenis', 30);
                $table->decimal('jumlah_delta', 14, 3);
                $table->decimal('stok_akhir', 14, 3)->default(0);
                $table->string('keterangan', 255)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['bahan_baku_id', 'created_at'], 'idx_bahan_mutasi_bahan_waktu');
                $table->index(['pesanan_id', 'jenis'], 'idx_bahan_mutasi_pesanan_jenis');
                $table->index('pembelian_bahan_id', 'idx_bahan_mutasi_pembelian');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan_stok_mutasi');
        Schema::dropIfExists('resep_menu');
        Schema::dropIfExists('pembelian_bahan_detail');
        Schema::dropIfExists('bahan_baku');
    }
};
