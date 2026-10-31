<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pesanan')) {
            Schema::create('pesanan', function (Blueprint $table) {
                $table->id();
                $table->string('kode_pesanan', 30)->unique();
                $table->string('kode_pembayaran', 30)->unique()->nullable();
                $table->foreignId('meja_id')->constrained('meja_makan')->restrictOnDelete();
                $table->foreignId('pelanggan_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('kasir_id')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('total_bayar', 12, 2)->default(0);
                $table->enum('status_pesanan', [
                    'menunggu', 'menunggu_verifikasi', 'menunggu_pembayaran', 'diproses', 'siap', 'diantar', 'selesai', 'dibatalkan',
                ])->default('menunggu');
                $table->enum('status_pembayaran', [
                    'belum_bayar', 'pending_verifikasi', 'pending_pembayaran', 'sudah_bayar',
                ])->default('belum_bayar');
                $table->enum('metode_pembayaran', ['tunai', 'qris'])->nullable();
                $table->string('catatan', 255)->nullable();
                $table->timestamp('waktu_pesan')->useCurrent();
                $table->foreignId('void_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('void_at')->nullable();
                $table->string('alasan_void', 191)->nullable();
                $table->timestamp('printed_at')->nullable()->after('alasan_void');
                $table->timestamps();
                $table->string('nama_pelanggan', 100)->nullable();

                $table->index(
                    ['status_pesanan', 'status_pembayaran'],
                    'idx_pesanan_status_antri'
                );

                $table->index(['meja_id', 'waktu_pesan'], 'idx_pesanan_meja_waktu');
                $table->index('waktu_pesan', 'idx_pesanan_waktu_pesan');
                $table->index('printed_at', 'idx_pesanan_printed_at');
            });
        } else {
            if (! Schema::hasColumn('pesanan', 'printed_at')) {
                Schema::table('pesanan', function (Blueprint $table) {
                    $table->timestamp('printed_at')->nullable()->after('alasan_void');
                });
            }

            if (! Schema::hasColumn('pesanan', 'nama_pelanggan')) {
                Schema::table('pesanan', function (Blueprint $table) {
                    $table->string('nama_pelanggan', 100)->nullable();
                });
            }

            if (! Schema::hasIndex('pesanan', 'idx_pesanan_printed_at')) {
                Schema::table('pesanan', function (Blueprint $table) {
                    $table->index('printed_at', 'idx_pesanan_printed_at');
                });
            }
        }

        DB::table('pesanan')
            ->where('status_pembayaran', 'sudah_bayar')
            ->whereNull('printed_at')
            ->update(['printed_at' => now()]);

        if (! Schema::hasTable('detail_pesanan')) {
            Schema::create('detail_pesanan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pesanan_id')->constrained('pesanan')->cascadeOnDelete();
                $table->foreignId('menu_id')->constrained('menu')->restrictOnDelete();
                $table->integer('jumlah');
                $table->decimal('harga_satuan', 12, 2);
                $table->decimal('subtotal', 12, 2);
                $table->string('catatan', 255)->nullable();
                $table->enum('status_item', [
                    'menunggu', 'diproses', 'siap', 'diantar',
                ])->default('menunggu');
                $table->timestamps();

                $table->index('status_item', 'idx_detail_status_item');
            });
        }

        if (! Schema::hasTable('pembayaran')) {
            Schema::create('pembayaran', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pesanan_id')->unique()->constrained('pesanan')->cascadeOnDelete();
                $table->enum('metode_pembayaran', ['tunai', 'qris']);
                $table->enum('status_pembayaran', [
                    'menunggu', 'lunas', 'gagal', 'dibatalkan',
                ])->default('menunggu');
                $table->decimal('jumlah_bayar', 12, 2);
                $table->decimal('kembalian', 12, 2)->default(0);
                $table->string('transaction_id', 100)->unique()->nullable();
                $table->string('order_id', 100)->unique()->nullable();
                $table->timestamp('waktu_bayar')->useCurrent();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('stok_mutasi')) {
            Schema::create('stok_mutasi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_id')->constrained('menu')->restrictOnDelete();
                $table->foreignId('pesanan_id')->nullable()->constrained('pesanan')->nullOnDelete();
                $table->enum('jenis', [
                    'penjualan', 'pembatalan', 'penyesuaian',
                ]);
                $table->integer('jumlah_delta');
                $table->integer('stok_akhir');
                $table->string('keterangan', 191)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['menu_id', 'created_at'], 'idx_stok_mutasi_menu');
            });
        }

        if (! Schema::hasTable('log_panggil_pelayan')) {
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

        if (! Schema::hasTable('sesi_meja')) {
            Schema::create('sesi_meja', function (Blueprint $table) {
                $table->id();
                $table->foreignId('meja_id')->constrained('meja_makan')->cascadeOnDelete();
                $table->string('kode_unik', 6);
                $table->enum('status', ['aktif', 'selesai'])->default('aktif');
                $table->timestamp('dibuka_pada')->nullable();
                $table->timestamp('ditutup_pada')->nullable();
                $table->timestamps();
                $table->string('nama_pelanggan', 100)->nullable();

                $table->index(['meja_id', 'status']);
            });
        } else {
            if (! Schema::hasColumn('sesi_meja', 'nama_pelanggan')) {
                Schema::table('sesi_meja', function (Blueprint $table) {
                    $table->string('nama_pelanggan', 100)->nullable();
                });
            }

            Schema::table('sesi_meja', function (Blueprint $table) {
                $table->string('kode_unik', 6)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('log_panggil_pelayan');
        Schema::dropIfExists('stok_mutasi');
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('detail_pesanan');
        Schema::dropIfExists('sesi_meja');
        Schema::dropIfExists('pesanan');
    }
};
