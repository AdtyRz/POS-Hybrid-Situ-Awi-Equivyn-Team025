<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengeluaran extends Model
{
    public const KATEGORI = [
        'bahan_bakar' => 'Bahan Bakar',
        'listrik_air' => 'Listrik & Air',
        'sewa' => 'Sewa',
        'gaji' => 'Gaji & Upah',
        'perawatan' => 'Perawatan',
        'perlengkapan' => 'Perlengkapan',
        'transportasi' => 'Transportasi',
        'lain_lain' => 'Lain-lain',
    ];

    protected $table = 'pengeluaran';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah' => 'decimal:2',
        ];
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function labelKategori(): string
    {
        return self::KATEGORI[$this->kategori] ?? ucfirst(str_replace('_', ' ', (string) $this->kategori));
    }
}
