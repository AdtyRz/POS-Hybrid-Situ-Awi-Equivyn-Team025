<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $role = $request->user()->role;

        $tujuan = [
            'admin' => '/admin/dashboard',
            'kasir' => '/kasir/dashboard',
            'koki' => '/kds/dapur',
            'barista' => '/kds/bar',
            'pelayan' => '/pelayan/panggilan',
            'pelanggan' => '/',
        ];

        return redirect($tujuan[$role] ?? '/');
    }
}