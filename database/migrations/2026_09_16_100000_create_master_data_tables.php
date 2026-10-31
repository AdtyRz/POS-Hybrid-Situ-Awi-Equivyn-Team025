<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('meja_makan')) {
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

            DB::statement('ALTER TABLE meja_makan ADD CONSTRAINT meja_makan_kapasitas_positif CHECK (kapasitas > 0)');
        }

        if (! Schema::hasTable('kategori')) {
            Schema::create('kategori', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kategori', 60);
                $table->string('target_kds', 10)->nullable();
                $table->string('deskripsi', 191)->nullable();
                $table->timestamps();
                $table->string('jenis', 20)->default('menu');

                $table->unique(['jenis', 'nama_kategori']);
            });
        } else {
            if (! Schema::hasColumn('kategori', 'jenis')) {
                Schema::table('kategori', function (Blueprint $table) {
                    $table->string('jenis', 20)->default('menu')->after('id');
                });
            }

            Schema::table('kategori', function (Blueprint $table) {
                $table->string('target_kds', 10)->nullable()->change();
            });

            if (Schema::hasIndex('kategori', 'kategori_nama_kategori_unique')) {
                Schema::table('kategori', function (Blueprint $table) {
                    $table->dropUnique('kategori_nama_kategori_unique');
                });
            }

            if (! Schema::hasIndex('kategori', 'kategori_jenis_nama_kategori_unique')) {
                Schema::table('kategori', function (Blueprint $table) {
                    $table->unique(['jenis', 'nama_kategori']);
                });
            }

            $this->gabungkanKategoriArea();
        }

        if (! Schema::hasTable('menu')) {
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
                $table->boolean('is_aktif')->default(true);

                $table->index('id', 'idx_menu_stok_habis')->where('stok_menu = 0');
            });
        } elseif (! Schema::hasColumn('menu', 'is_aktif')) {
            Schema::table('menu', function (Blueprint $table) {
                $table->boolean('is_aktif')->default(true)->after('stok_menu');
            });
        }

        if (! Schema::hasTable('pengaturan')) {
            Schema::create('pengaturan', function (Blueprint $table) {
                $table->id();
                $table->string('kunci', 100)->unique();
                $table->text('nilai')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
        Schema::dropIfExists('menu');
        Schema::dropIfExists('kategori');
        Schema::dropIfExists('meja_makan');
    }

    private function gabungkanKategoriArea(): void
    {
        if (! Schema::hasTable('kategori_area') || ! Schema::hasColumn('kategori_area', 'nama_area')) {
            return;
        }

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
};
