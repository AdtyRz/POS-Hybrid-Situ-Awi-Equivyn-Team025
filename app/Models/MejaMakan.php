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

    public function punyaSessionAktif(): bool
    {
        return $this->session_code !== null && $this->session_ended_at === null;
    }

    public function tutupSession(?int $userId = null): void
    {
        $this->update([
            'session_code' => null,
            'session_started_at' => null,
            'session_ended_at' => now(),
            'session_ended_by' => $userId,
            'status_meja' => 'kosong',
        ]);
    }

    public function mulaiSession(string $kodeSession): void
    {
        $this->update([
            'session_code' => $kodeSession,
            'session_started_at' => now(),
            'session_ended_at' => null,
            'session_ended_by' => null,
            'status_meja' => 'terisi',
        ]);
    }
}
