<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pesanan yang sudah ada sebelum fitur pelacakan struk diperkenalkan
     * dianggap sudah pernah dicetak. Dengan begitu struk lama tidak muncul
     * sebagai riwayat pada cetak struk pengunjung berikutnya.
     */
    public function up(): void
    {
        DB::table('pesanan')
            ->where('status_pembayaran', 'sudah_bayar')
            ->whereNull('printed_at')
            ->update(['printed_at' => now()]);
    }

    public function down(): void
    {
        // Tidak mengembalikan null karena akan memunculkan kembali struk lama.
    }
};
