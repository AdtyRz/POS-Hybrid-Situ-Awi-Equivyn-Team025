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




        if (strlen((string) $sesi->kode_unik) !== static::panjangKode()) {
            $sesi->update([
                'kode_unik' => static::kodeUnikBaru($sesi->kode_unik),
            ]);
        }

        return $sesi;
    }




    public static function panjangKode(): int
    {
        return Pengaturan::nilai('encryption_level') === '6 Digit' ? 6 : 4;
    }






    public static function tutupSesiLintasHari(): int
    {
        $awalHari = now('Asia/Jakarta')->startOfDay();

        $sesiBasi = static::query()
            ->where('status', 'aktif')
            ->where('dibuka_pada', '<', $awalHari)
            ->get()
            ->reject(fn (self $sesi) => Pesanan::query()
                ->where('meja_id', $sesi->meja_id)
                ->whereNotIn('status_pesanan', [Pesanan::STATUS_SELESAI, Pesanan::STATUS_DIBATALKAN])
                ->exists());

        $sesiBasi->each(fn (self $sesi) => static::tutupKarenaKedaluwarsa($sesi));

        return $sesiBasi->count();
    }

    private static function perluDitutupOtomatis(self $sesi): bool
    {
        $lintasHari = $sesi->dibuka_pada !== null
            && $sesi->dibuka_pada->lt(now('Asia/Jakarta')->startOfDay());

        if (! $lintasHari && ! filter_var(Pengaturan::nilai('session_auto_close'), FILTER_VALIDATE_BOOLEAN)) {
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

        if ($lintasHari) {
            return true;
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


            Pesanan::query()
                ->where('meja_id', $sesi->meja_id)
                ->where('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR)
                ->whereNull('printed_at')
                ->update(['printed_at' => now()]);

            $sesi->meja?->update(['status_meja' => 'kosong']);
        });
    }





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









    public static function sinkronkanStatusMeja(int $mejaId): void
    {
        $sesi = static::query()
            ->where('meja_id', $mejaId)
            ->where('status', 'aktif')
            ->latest('id')
            ->first();

        $query = Pesanan::query()->where('meja_id', $mejaId);

        if ($sesi && $sesi->dibuka_pada !== null) {
            $query->where('status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN)
                ->where(function ($q) use ($sesi) {
                    $q->whereNotIn('status_pesanan', [
                        Pesanan::STATUS_SELESAI,
                        Pesanan::STATUS_DIBATALKAN,
                    ])->orWhere('waktu_pesan', '>=', $sesi->dibuka_pada);
                });
        } else {
            $query->whereNotIn('status_pesanan', [
                Pesanan::STATUS_SELESAI,
                Pesanan::STATUS_DIBATALKAN,
            ]);
        }

        MejaMakan::whereKey($mejaId)->update([
            'status_meja' => $query->exists() ? 'terisi' : 'kosong',
        ]);
    }





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


        return str_pad((string) random_int(0, $maks), $digit, '0', STR_PAD_LEFT);
    }
}
