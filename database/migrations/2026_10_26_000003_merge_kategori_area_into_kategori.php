<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori', function (Blueprint $table) {
            $table->string('jenis', 20)->default('menu')->after('id');
            $table->string('target_kds', 10)->nullable()->change();
        });

        Schema::table('kategori', function (Blueprint $table) {
            $table->dropUnique('kategori_nama_kategori_unique');
            $table->unique(['jenis', 'nama_kategori']);
        });

        if (Schema::hasTable('kategori_area')) {
            $now = now();

            DB::table('kategori_area')
                ->orderBy('id')
                ->get()
                ->each(function ($area) use ($now) {
                    $sudahAda = DB::table('kategori')
                        ->where('jenis', 'area')
                        ->where('nama_kategori', $area->nama_area)
                        ->exists();

                    if (! $sudahAda) {
                        DB::table('kategori')->insert([
                            'jenis' => 'area',
                            'nama_kategori' => $area->nama_area,
                            'target_kds' => null,
                            'deskripsi' => null,
                            'created_at' => $area->created_at ?? $now,
                            'updated_at' => $area->updated_at ?? $now,
                        ]);
                    }
                });

            Schema::dropIfExists('kategori_area');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('kategori_area')) {
            Schema::create('kategori_area', function (Blueprint $table) {
                $table->id();
                $table->string('nama_area', 100)->unique();
                $table->timestamps();
            });
        }

        DB::table('kategori')
            ->where('jenis', 'area')
            ->orderBy('id')
            ->get()
            ->each(function ($area) {
                DB::table('kategori_area')->insertOrIgnore([
                    'nama_area' => $area->nama_kategori,
                    'created_at' => $area->created_at,
                    'updated_at' => $area->updated_at,
                ]);
            });

        DB::table('kategori')->where('jenis', 'area')->delete();

        Schema::table('kategori', function (Blueprint $table) {
            $table->dropUnique(['jenis', 'nama_kategori']);
            $table->unique('nama_kategori');
            $table->dropColumn('jenis');
            $table->string('target_kds', 10)->nullable(false)->change();
        });
    }
};
