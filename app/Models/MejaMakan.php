<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MejaMakan extends Model
{
    protected $table = 'meja_makan';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'kapasitas' => 'integer',
        ];
    }

    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'meja_id');
    }

    public function logPanggil(): HasMany
    {
        return $this->hasMany(LogPanggilPelayan::class, 'meja_id');
    }

    public function pesananAktif(): HasMany
    {
        return $this->pesanan()->whereIn('status_pesanan', [
            'menunggu',
            'diproses',
            'siap',
            'diantar',
        ]);
    }

    public function bisaDipakai(): bool
    {
        return $this->pesananAktif()->count() === 0;
    }
}
