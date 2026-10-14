<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_meja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meja_id')->constrained('meja_makan')->cascadeOnDelete();
            $table->string('kode_unik', 4);
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->timestamp('dibuka_pada')->nullable();
            $table->timestamp('ditutup_pada')->nullable();
            $table->timestamps();

            $table->index(['meja_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_meja');
    }
};
