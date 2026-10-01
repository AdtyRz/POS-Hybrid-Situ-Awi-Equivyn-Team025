<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogPanggilPelayan extends Model
{
    protected $table = 'log_panggil_pelayan';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'waktu_panggil' => 'datetime',
            'waktu_selesai' => 'datetime',
        ];
    }

    public function meja(): BelongsTo
    {
        return $this->belongsTo(MejaMakan::class, 'meja_id');
    }

    public function pelayan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelayan_id');
    }
}
