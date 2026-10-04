<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;

class EmenuController extends Controller
{
    public function menu()
    {
        $menu = Menu::where('stok_menu', '>', 0)
            ->with('kategori')
            ->orderBy('kategori_id')
            ->orderBy('nama_menu')
            ->get();

        return response()->json([
            'status' => 'sukses',
            'total' => $menu->count(),
            'data' => $menu,
        ], 200);
    }

    public function meja()
    {
        $meja = MejaMakan::where('status_meja', '!=', 'perbaikan')
            ->orderBy('kode_meja')
            ->get(['id', 'kode_meja', 'area', 'kapasitas', 'status_meja']);

        return response()->json([
            'status' => 'sukses',
            'total' => $meja->count(),
            'data' => $meja,
        ], 200);
    }

    public function kategori()
    {
        $kategori = Kategori::orderBy('nama_kategori')->get();

        return response()->json([
            'status' => 'sukses',
            'total' => $kategori->count(),
            'data' => $kategori,
        ], 200);
    }
}