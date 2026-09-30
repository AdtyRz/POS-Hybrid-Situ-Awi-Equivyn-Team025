<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Menu;

class Kategori extends Model
{
    protected $table = 'Kategori';

    public function menu()
    {
        return $this->hasMany(Menu::class, 'kategori_id');
    }
}
