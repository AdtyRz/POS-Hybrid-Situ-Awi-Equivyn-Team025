<?php

namespace App\Models;

use App\Services\ShiftService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatShift extends Model
{
    protected $table = 'riwayat_shift';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'mulai_pada' => 'datetime',
            'terakhir_aktivitas' => 'datetime',
            'selesai_pada' => 'datetime',
            'durasi_detik' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function durasiTeks(): string
    {
        return ShiftService::durasiTeks($this->durasi_detik);
    }
}
