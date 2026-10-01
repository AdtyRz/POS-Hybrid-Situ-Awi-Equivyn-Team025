<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->index('id', 'idx_menu_stok_habis')->where('stok_menu = 0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
