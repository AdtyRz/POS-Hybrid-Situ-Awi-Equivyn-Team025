<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EMenuController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EMenuController::class, 'beranda'])->name('emenu.index');

Route::get('/meja/{meja}', [EMenuController::class, 'aplikasi'])->name('emenu.meja');
Route::post('/meja/{meja}/panggil', [EMenuController::class, 'panggil'])->name('emenu.panggil');
Route::post('/meja/{meja}/checkout', [EMenuController::class, 'checkout'])->name('emenu.checkout');
Route::get('/pesanan/{kode_pesanan}', [EMenuController::class, 'status'])->name('emenu.status');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/auth.php';
