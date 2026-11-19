<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembelianBahanDetail extends Model
{
    protected $table = 'pembelian_bahan_detail';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:3',
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(PembelianBahan::class, 'pembelian_bahan_id');
    }

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
