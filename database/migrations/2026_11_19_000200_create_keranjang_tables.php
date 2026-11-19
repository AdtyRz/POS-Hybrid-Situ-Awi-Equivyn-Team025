<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('keranjang')) {
            Schema::create('keranjang', function (Blueprint $table) {
                $table->id();
                $table->foreignId('meja_id')->constrained('meja_makan')->cascadeOnDelete();
                $table->foreignId('sesi_meja_id')->constrained('sesi_meja')->cascadeOnDelete();
                $table->string('perangkat_id', 64);
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->unique(['sesi_meja_id', 'perangkat_id'], 'keranjang_sesi_perangkat_unik');
                $table->index('expires_at', 'idx_keranjang_expires');
            });
        }

        if (! Schema::hasTable('keranjang_item')) {
            Schema::create('keranjang_item', function (Blueprint $table) {
                $table->id();
                $table->foreignId('keranjang_id')->constrained('keranjang')->cascadeOnDelete();
                $table->foreignId('menu_id')->constrained('menu')->cascadeOnDelete();
                $table->unsignedInteger('jumlah');
                $table->string('catatan', 255)->nullable();
                $table->timestamps();

                $table->unique(['keranjang_id', 'menu_id'], 'keranjang_item_unik');
            });
        }

        if (Schema::hasTable('bahan_stok_mutasi') && ! Schema::hasColumn('bahan_stok_mutasi', 'keranjang_item_id')) {
            Schema::table('bahan_stok_mutasi', function (Blueprint $table) {
                $table->foreignId('keranjang_item_id')
                    ->nullable()
                    ->after('pembelian_bahan_id')
                    ->constrained('keranjang_item')
                    ->nullOnDelete();

                $table->index('keranjang_item_id', 'idx_bahan_mutasi_keranjang');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bahan_stok_mutasi') && Schema::hasColumn('bahan_stok_mutasi', 'keranjang_item_id')) {
            Schema::table('bahan_stok_mutasi', function (Blueprint $table) {
                $table->dropForeign(['keranjang_item_id']);
                $table->dropIndex('idx_bahan_mutasi_keranjang');
                $table->dropColumn('keranjang_item_id');
            });
        }

        Schema::dropIfExists('keranjang_item');
        Schema::dropIfExists('keranjang');
    }
};
