<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bahan_baku', 'satuan_resep')) {
            Schema::table('bahan_baku', function (Blueprint $table) {
                $table->string('satuan_resep', 20)->nullable()->after('satuan');
            });
        }

        if (! Schema::hasColumn('bahan_baku', 'faktor_resep')) {
            Schema::table('bahan_baku', function (Blueprint $table) {
                $table->decimal('faktor_resep', 14, 3)->default(1)->after('satuan_resep');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bahan_baku', 'faktor_resep')) {
            Schema::table('bahan_baku', function (Blueprint $table) {
                $table->dropColumn('faktor_resep');
            });
        }

        if (Schema::hasColumn('bahan_baku', 'satuan_resep')) {
            Schema::table('bahan_baku', function (Blueprint $table) {
                $table->dropColumn('satuan_resep');
            });
        }
    }
};
