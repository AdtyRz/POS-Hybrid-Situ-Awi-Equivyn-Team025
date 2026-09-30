<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MejaMakan extends Model
{
    protected $table = 'meja_makan';
    protected $guarded = ['id'];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_meja', 'id');
    }

    public function logPanggil()
    {
        return $this->hasMany(LogPanggilPelayan::class, 'id_meja', 'id');
    }
}
