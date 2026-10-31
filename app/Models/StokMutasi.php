<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    protected $table = 'stok_mutasi';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'jumlah_delta' => 'integer',
            'stok_akhir' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
