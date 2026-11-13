<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sesi_meja') && ! Schema::hasColumn('sesi_meja', 'perangkat_id')) {
            Schema::table('sesi_meja', function (Blueprint $table) {
                $table->string('perangkat_id', 64)->nullable()->index();
                $table->string('perangkat_label', 100)->nullable();
                $table->timestamp('perangkat_aktif_pada')->nullable();
                $table->string('takeover_status', 20)->nullable();
                $table->string('takeover_perangkat_id', 64)->nullable();
                $table->string('takeover_perangkat_label', 100)->nullable();
                $table->timestamp('takeover_diminta_pada')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sesi_meja', 'perangkat_id')) {
            Schema::table('sesi_meja', function (Blueprint $table) {
                $table->dropIndex(['perangkat_id']);
                $table->dropColumn([
                    'perangkat_id',
                    'perangkat_label',
                    'perangkat_aktif_pada',
                    'takeover_status',
                    'takeover_perangkat_id',
                    'takeover_perangkat_label',
                    'takeover_diminta_pada',
                ]);
            });
        }
    }
};
