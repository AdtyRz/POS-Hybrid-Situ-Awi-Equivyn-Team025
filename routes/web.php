<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pelanggan.index');
})->name('emenu.index');

Route::prefix('pelanggan')->name('pelanggan.')->group(function () {
    Route::get('/', function () {
        return view('pelanggan.index');
    })->name('index');

    Route::get('/katalog', function () {
        return view('pelanggan.katalog');
    })->name('katalog');

    Route::get('/ringkasan', function () {
        return view('pelanggan.ringkasan');
    })->name('ringkasan');

    Route::get('/pembayaran', function () {
        return view('pelanggan.pembayaran');
    })->name('pembayaran');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/auth.php';
