<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('riwayat_shift', 'toleransi_menit')) {
            Schema::table('riwayat_shift', function (Blueprint $table) {
                $table->unsignedSmallInteger('toleransi_menit')->default(15);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('riwayat_shift', 'toleransi_menit')) {
            Schema::table('riwayat_shift', function (Blueprint $table) {
                $table->dropColumn('toleransi_menit');
            });
        }
    }
};
