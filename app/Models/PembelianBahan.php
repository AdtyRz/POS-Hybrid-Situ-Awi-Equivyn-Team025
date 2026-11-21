<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembelianBahan extends Model
{
    protected $table = 'pembelian_bahan';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PembelianBahanDetail::class, 'pembelian_bahan_id');
    }

    public function mutasi(): HasMany
    {
        return $this->hasMany(BahanStokMutasi::class, 'pembelian_bahan_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
