<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keranjang extends Model
{
    protected $table = 'keranjang';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function meja(): BelongsTo
    {
        return $this->belongsTo(MejaMakan::class, 'meja_id');
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiMeja::class, 'sesi_meja_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(KeranjangItem::class, 'keranjang_id');
    }

    public function kedaluwarsa(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
