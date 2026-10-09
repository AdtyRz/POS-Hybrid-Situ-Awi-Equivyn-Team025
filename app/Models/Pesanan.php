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

    protected $fillable = [
        'kode_pesanan',
        'meja_id',
        'session_code',
        'pelanggan_id',
        'kasir_id',
        'total_bayar',
        'status_pesanan',
        'status_pembayaran',
        'metode_pembayaran',
        'catatan',
        'waktu_pesan',
        'void_by',
        'void_at',
        'alasan_void',
    ];

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SIAP = 'siap';
    public const STATUS_DIANTAR = 'diantar';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIBATALKAN = 'dibatalkan';

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
            self::STATUS_DIPROSES,
            self::STATUS_SIAP,
            self::STATUS_DIANTAR,
        ], true);
    }
}
