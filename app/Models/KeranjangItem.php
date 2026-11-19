<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeranjangItem extends Model
{
    protected $table = 'keranjang_item';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function keranjang(): BelongsTo
    {
        return $this->belongsTo(Keranjang::class, 'keranjang_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function bahanMutasi(): HasMany
    {
        return $this->hasMany(BahanStokMutasi::class, 'keranjang_item_id');
    }
}
