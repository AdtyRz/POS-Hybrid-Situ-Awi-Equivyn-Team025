<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BahanBaku extends Model
{
    protected $table = 'bahan_baku';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'stok' => 'decimal:3',
            'stok_minimum' => 'decimal:3',
            'harga_rata_rata' => 'decimal:2',
            'is_aktif' => 'boolean',
        ];
    }

    public function pembelianDetail(): HasMany
    {
        return $this->hasMany(PembelianBahanDetail::class, 'bahan_baku_id');
    }

    public function resep(): HasMany
    {
        return $this->hasMany(ResepMenu::class, 'bahan_baku_id');
    }

    public function mutasi(): HasMany
    {
        return $this->hasMany(BahanStokMutasi::class, 'bahan_baku_id');
    }

    public function nilaiStok(): float
    {
        return (float) $this->stok * (float) $this->harga_rata_rata;
    }

    public function menipis(): bool
    {
        return (float) $this->stok <= (float) $this->stok_minimum;
    }
}
