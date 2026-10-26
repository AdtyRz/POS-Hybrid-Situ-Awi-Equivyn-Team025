<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    protected $table = 'kategori';

    protected $guarded = ['id'];

    public function menu(): HasMany
    {
        return $this->hasMany(Menu::class, 'kategori_id');
    }

    public function scopeUntukMenu($query)
    {
        return $query->where('jenis', 'menu');
    }

    public function scopeUntukArea($query)
    {
        return $query->where('jenis', 'area');
    }
}
