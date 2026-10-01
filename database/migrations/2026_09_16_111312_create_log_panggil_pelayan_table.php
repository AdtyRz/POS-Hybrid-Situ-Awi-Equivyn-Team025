<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_panggil_pelayan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meja_id')->constrained('meja_makan')->restrictOnDelete();
            $table->foreignId('pelayan_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_panggilan', ['menunggu', 'ditangani', 'selesai'])
                ->default('menunggu');
            $table->timestamp('waktu_panggil')->useCurrent();
            $table->timestamp('waktu_selesai')->nullable();
            $table->timestamps();

            $table->index(
                ['status_panggilan', 'waktu_panggil'],
                'idx_log_panggil_antrian'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_panggil_pelayan');
    }
};
