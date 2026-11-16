<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->gantiCheck('pesanan', ['tunai', 'qris', 'transfer']);
        $this->gantiCheck('pembayaran', ['tunai', 'qris', 'transfer']);
    }

    public function down(): void
    {
        DB::table('pesanan')
            ->where('metode_pembayaran', 'transfer')
            ->update(['metode_pembayaran' => 'tunai']);

        DB::table('pembayaran')
            ->where('metode_pembayaran', 'transfer')
            ->update(['metode_pembayaran' => 'tunai']);

        $this->gantiCheck('pesanan', ['tunai', 'qris']);
        $this->gantiCheck('pembayaran', ['tunai', 'qris']);
    }

    private function gantiCheck(string $tabel, array $nilai): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $checks = DB::select("
            SELECT conname
            FROM pg_constraint
            WHERE conrelid = '{$tabel}'::regclass
              AND contype = 'c'
              AND pg_get_constraintdef(oid) ILIKE '%metode_pembayaran%'
        ");

        foreach ($checks as $check) {
            DB::statement("ALTER TABLE \"{$tabel}\" DROP CONSTRAINT \"{$check->conname}\"");
        }

        $daftar = implode(', ', array_map(fn ($v) => "'{$v}'", $nilai));

        DB::statement("
            ALTER TABLE \"{$tabel}\"
            ADD CONSTRAINT \"{$tabel}_metode_pembayaran_check\"
            CHECK (\"metode_pembayaran\" IN ({$daftar}))
        ");
    }
};
