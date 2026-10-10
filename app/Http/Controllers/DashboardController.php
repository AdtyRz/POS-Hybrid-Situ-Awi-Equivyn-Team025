<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $tujuan = [
            'admin' => '/admin/dashboard',
            'kasir' => '/kasir/dashboard',
            'koki' => '/kds/dapur',
            'barista' => '/kds/bar',
            'pelayan' => '/pelayan/panggilan',
            'pelanggan' => '/',
        ];

        return redirect($tujuan[$request->user()->role] ?? '/');
    }
}
