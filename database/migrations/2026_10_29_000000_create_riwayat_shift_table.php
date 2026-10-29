<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_shift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20);
            $table->string('session_id')->nullable()->index();
            $table->string('nama_shift', 50)->nullable();
            $table->time('jadwal_mulai')->nullable();
            $table->time('jadwal_selesai')->nullable();
            $table->timestamp('mulai_pada');
            $table->timestamp('terakhir_aktivitas')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->unsignedInteger('durasi_detik')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['role', 'mulai_pada']);
            $table->index(['user_id', 'selesai_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_shift');
    }
};
