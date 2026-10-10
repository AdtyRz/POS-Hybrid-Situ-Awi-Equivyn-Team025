<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        $menu = Menu::with('kategori')
            ->where('stok_menu', '>', 0)
            ->orderBy('nama_menu')
            ->get();

        return ApiResponse::success($menu, 'Berhasil mengambil daftar menu', 200);
    }
}
