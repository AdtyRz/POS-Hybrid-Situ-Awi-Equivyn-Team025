<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $guarded = ['id'];

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SIAP = 'siap';
    public const STATUS_DIANTAR = 'diantar';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const PAYMENT_BELUM_BAYAR = 'belum_bayar';
    public const PAYMENT_PENDING_VERIFIKASI = 'pending_verifikasi';
    public const PAYMENT_PENDING_PEMBAYARAN = 'pending_pembayaran';
    public const PAYMENT_SUDAH_BAYAR = 'sudah_bayar';

    protected function casts(): array
    {
        return [
            'total_bayar' => 'decimal:2',
            'waktu_pesan' => 'datetime',
            'void_at' => 'datetime',
        ];
    }

    public function meja(): BelongsTo
    {
        return $this->belongsTo(MejaMakan::class, 'meja_id');
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelanggan_id');
    }

    public function pembatal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'void_by');
    }

    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'pesanan_id');
    }

    public function stokMutasi(): HasMany
    {
        return $this->hasMany(StokMutasi::class, 'pesanan_id');
    }

    public function masihAktif(): bool
    {
        return in_array($this->status_pesanan, [
            self::STATUS_MENUNGGU,
            self::STATUS_MENUNGGU_VERIFIKASI,
            self::STATUS_MENUNGGU_PEMBAYARAN,
            self::STATUS_DIPROSES,
            self::STATUS_SIAP,
            self::STATUS_DIANTAR,
        ], true);
    }
}
