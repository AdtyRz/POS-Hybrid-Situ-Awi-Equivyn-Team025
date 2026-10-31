<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SesiMeja extends Model
{
    protected $table = 'sesi_meja';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'dibuka_pada' => 'datetime',
            'ditutup_pada' => 'datetime',
        ];
    }

    public function meja(): BelongsTo
    {
        return $this->belongsTo(MejaMakan::class, 'meja_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Ambil sesi aktif meja. Bila belum ada, buat sesi baru lengkap dengan kode unik.
     * Dengan begitu tiap meja selalu punya kode yang tampil di LCD.
     *
     * Saat pengelolaan otomatis aktif, sesi yang sudah melewati batas waktu
     * tanpa transaksi akan ditutup dan diganti kode baru. Sesi yang masih
     * memiliki pesanan aktif tidak pernah ditutup otomatis agar tagihan tamu
     * tidak berubah menjadi lunas tanpa pembayaran.
     */
    public static function aktifUntuk(MejaMakan $meja): self
    {
        $sesi = static::query()
            ->where('meja_id', $meja->id)
            ->where('status', 'aktif')
            ->latest('id')
            ->first();

        if ($sesi && static::perluDitutupOtomatis($sesi)) {
            static::tutupKarenaKedaluwarsa($sesi);
            $sesi = null;
        }

        if (! $sesi) {
            return static::bukaBaru($meja);
        }

        // Samakan panjang kode sesi yang sedang berjalan dengan pengaturan
        // terbaru agar LCD, halaman kunci, dan master meja tidak tertinggal
        // memakai kode 4 digit setelah admin beralih ke 6 digit (atau sebaliknya).
        if (strlen((string) $sesi->kode_unik) !== static::panjangKode()) {
            $sesi->update([
                'kode_unik' => static::kodeUnikBaru($sesi->kode_unik),
            ]);
        }

        return $sesi;
    }

    /**
     * Panjang kode sesi sesuai pengaturan enkripsi (4 atau 6 digit).
     */
    public static function panjangKode(): int
    {
        return Pengaturan::nilai('encryption_level') === '6 Digit' ? 6 : 4;
    }

    private static function perluDitutupOtomatis(self $sesi): bool
    {
        if (! filter_var(Pengaturan::nilai('session_auto_close'), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        $menit = max(1, (int) Pengaturan::nilai('session_expiry_minutes', 120));

        $adaPesananAktif = Pesanan::query()
            ->where('meja_id', $sesi->meja_id)
            ->whereNotIn('status_pesanan', [Pesanan::STATUS_SELESAI, Pesanan::STATUS_DIBATALKAN])
            ->exists();

        if ($adaPesananAktif) {
            return false;
        }

        $aktivitasTerakhir = Pesanan::query()
            ->where('meja_id', $sesi->meja_id)
            ->where('status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN)
            ->max('waktu_pesan');

        $acuan = $aktivitasTerakhir
            ? Carbon::parse($aktivitasTerakhir, config('app.timezone'))
            : $sesi->dibuka_pada;

        return $acuan !== null && $acuan->copy()->addMinutes($menit)->isPast();
    }

    private static function tutupKarenaKedaluwarsa(self $sesi): void
    {
        DB::transaction(function () use ($sesi) {
            $sesi->update([
                'status' => 'selesai',
                'ditutup_pada' => now(),
            ]);

            // Struk milik sesi lama tidak boleh terbawa ke pengunjung berikutnya.
            Pesanan::query()
                ->where('meja_id', $sesi->meja_id)
                ->where('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR)
                ->whereNull('printed_at')
                ->update(['printed_at' => now()]);

            $sesi->meja?->update(['status_meja' => 'kosong']);
        });
    }

    /**
     * Buat sesi baru (aktif) untuk meja berikut kode unik sesuai pengaturan
     * panjang digit (4 atau 6 digit).
     */
    public static function bukaBaru(MejaMakan $meja): self
    {
        $sebelumnya = static::query()
            ->where('meja_id', $meja->id)
            ->latest('id')
            ->value('kode_unik');

        return static::create([
            'meja_id' => $meja->id,
            'kode_unik' => static::kodeUnikBaru($sebelumnya),
            'status' => 'aktif',
            'dibuka_pada' => now(),
        ]);
    }

    /**
     * Tutup sesi aktif meja lalu buka sesi baru dengan kode unik yang baru.
     * Hanya pesanan yang sudah dibayar yang boleh ditutup. Stok sudah dipotong
     * pada saat pembayaran, jadi proses ini tidak memotong stok lagi.
     *
     * @throws \RuntimeException bila masih ada pesanan aktif yang belum dibayar.
     */
    public static function putuskanSesi(MejaMakan $meja): self
    {
        return DB::transaction(function () use ($meja) {
            $pesananAktif = Pesanan::where('meja_id', $meja->id)
                ->whereNotIn('status_pesanan', ['selesai', 'dibatalkan'])
                ->get();

            $belumDibayar = $pesananAktif->filter(
                fn (Pesanan $pesanan) => $pesanan->status_pembayaran !== Pesanan::PAYMENT_SUDAH_BAYAR
            );

            if ($belumDibayar->isNotEmpty()) {
                throw new \RuntimeException(
                    'Sesi belum dapat diakhiri karena masih ada ' . $belumDibayar->count() . ' pesanan yang belum dibayar.'
                );
            }

            Pesanan::whereIn('id', $pesananAktif->pluck('id'))->update([
                'status_pesanan' => Pesanan::STATUS_SELESAI,
            ]);

            // Tutup struk sesi ini agar tidak muncul pada pengunjung berikutnya.
            Pesanan::query()
                ->where('meja_id', $meja->id)
                ->where('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR)
                ->whereNull('printed_at')
                ->update(['printed_at' => now()]);

            $meja->update(['status_meja' => 'kosong']);

            static::query()
                ->where('meja_id', $meja->id)
                ->where('status', 'aktif')
                ->update([
                    'status' => 'selesai',
                    'ditutup_pada' => now(),
                ]);

            return static::bukaBaru($meja);
        });
    }

    /**
     * Kode unik sesi sesuai pengaturan panjang digit (4 atau 6 digit) yang
     * tidak dipakai sesi aktif mana pun dan berbeda dari kode sebelumnya.
     */
    public static function kodeUnikBaru(?string $kecuali = null): string
    {
        $digit = static::panjangKode();
        $maks = (10 ** $digit) - 1;

        for ($i = 0; $i < 50; $i++) {
            $kode = str_pad((string) random_int(0, $maks), $digit, '0', STR_PAD_LEFT);

            if ($kecuali !== null && $kode === $kecuali) {
                continue;
            }

            $dipakai = static::query()
                ->where('status', 'aktif')
                ->where('kode_unik', $kode)
                ->exists();

            if (! $dipakai) {
                return $kode;
            }
        }

        // Fallback sangat jarang: pakai angka acak apa adanya.
        return str_pad((string) random_int(0, $maks), $digit, '0', STR_PAD_LEFT);
    }
}
