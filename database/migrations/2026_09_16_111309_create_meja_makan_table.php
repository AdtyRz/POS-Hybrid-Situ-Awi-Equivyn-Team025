<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
