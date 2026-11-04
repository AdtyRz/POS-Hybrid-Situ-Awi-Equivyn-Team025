<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MejaMakan extends Model
{
    protected $table = 'meja_makan';

    protected $guarded = ['id'];

    protected $hidden = [
        'qr_token',
        'id_device',
    ];

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

    /**
     * Pesanan yang tercatat pada sesi berjalan, termasuk yang sudah selesai.
     * Dipakai halaman Sesi Meja agar riwayat sesi tidak hilang sebelum kasir
     * menekan "Akhiri Sesi".
     */
    public function pesananSesi(): HasMany
    {
        return $this->pesanan()
            ->where('status_pesanan', '!=', 'dibatalkan')
            ->orderBy('waktu_pesan');
    }
}
