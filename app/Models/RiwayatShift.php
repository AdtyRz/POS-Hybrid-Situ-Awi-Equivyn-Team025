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
            'toleransi_menit' => 'integer',
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

    /**
     * @return array{menit:int,terlambat:bool,dalam_toleransi:bool,label:string,kelas:string}
     */
    public function keterlambatan(): array
    {
        return ShiftService::keterlambatan(
            $this->jadwal_mulai,
            $this->jadwal_selesai,
            $this->mulai_pada,
            $this->toleransi_menit === null ? null : (int) $this->toleransi_menit
        );
    }

    public function statusKehadiran(): string
    {
        return $this->keterlambatan()['label'];
    }

    public function terlambat(): bool
    {
        return $this->keterlambatan()['terlambat'];
    }
}
