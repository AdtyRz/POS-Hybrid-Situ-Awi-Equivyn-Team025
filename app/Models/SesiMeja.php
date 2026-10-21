<?php

namespace App\Models;

use App\Services\PesananService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     */
    public static function aktifUntuk(MejaMakan $meja): self
    {
        return static::query()
            ->where('meja_id', $meja->id)
            ->where('status', 'aktif')
            ->latest('id')
            ->first()
            ?? static::bukaBaru($meja);
    }

    /**
     * Buat sesi baru (aktif) untuk meja berikut kode unik 4 digit.
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
     * Pesanan yang sudah lunas ditandai selesai; pesanan yang belum dibayar
     * dibatalkan (stok dikembalikan) agar status pembayaran tidak dipalsukan.
     */
    public static function putuskanSesi(MejaMakan $meja): self
    {
        return DB::transaction(function () use ($meja) {
            $pesananAktif = Pesanan::where('meja_id', $meja->id)
                ->whereNotIn('status_pesanan', [Pesanan::STATUS_SELESAI, Pesanan::STATUS_DIBATALKAN])
                ->get();

            foreach ($pesananAktif as $pesanan) {
                if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
                    $pesanan->update(['status_pesanan' => Pesanan::STATUS_SELESAI]);

                    continue;
                }

                app(PesananService::class)->batalkan(
                    $pesanan,
                    null,
                    'Sesi meja ditutup tanpa pembayaran.'
                );
            }

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
     * Kode unik 4 digit yang tidak dipakai sesi aktif mana pun dan berbeda
     * dari kode sesi sebelumnya bila diberikan.
     */
    public static function kodeUnikBaru(?string $kecuali = null): string
    {
        for ($i = 0; $i < 50; $i++) {
            $kode = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

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
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
