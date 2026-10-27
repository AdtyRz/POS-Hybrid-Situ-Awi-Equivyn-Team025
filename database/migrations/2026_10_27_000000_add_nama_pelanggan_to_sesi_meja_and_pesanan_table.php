<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesi_meja', function (Blueprint $table) {
            $table->string('nama_pelanggan', 100)->nullable();
        });

        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('nama_pelanggan', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn('nama_pelanggan');
        });

        Schema::table('sesi_meja', function (Blueprint $table) {
            $table->dropColumn('nama_pelanggan');
        });
    }
};
