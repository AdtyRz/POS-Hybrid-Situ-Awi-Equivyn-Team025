<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IotController;
use App\Http\Controllers\KdsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('emenu.index');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/admin/menu', [AdminController::class, 'daftarMenu'])->name('admin.menu');
    Route::post('/admin/menu', [AdminController::class, 'simpanMenu'])->name('admin.menu.simpan');
    Route::put('/admin/menu/{id}', [AdminController::class, 'ubahMenu'])->name('admin.menu.ubah');
    Route::delete('/admin/menu/{id}', [AdminController::class, 'hapusMenu'])->name('admin.menu.hapus');

    Route::get('/admin/kategori', [AdminController::class, 'daftarKategori'])->name('admin.kategori');
    Route::post('/admin/kategori', [AdminController::class, 'simpanKategori'])->name('admin.kategori.simpan');

    Route::get('/admin/meja', [AdminController::class, 'daftarMeja'])->name('admin.meja');
    Route::post('/admin/meja', [AdminController::class, 'simpanMeja'])->name('admin.meja.simpan');

    Route::get('/admin/akun', [AdminController::class, 'daftarAkun'])->name('admin.akun');
    Route::post('/admin/akun', [AdminController::class, 'simpanAkun'])->name('admin.akun.simpan');
    Route::patch('/admin/akun/{id}', [AdminController::class, 'ubahStatusAkun'])->name('admin.akun.status');
});

Route::middleware(['auth', 'role:kasir'])->group(function () {
    Route::get('/kasir/dashboard', [KdsController::class, 'daftarPesanan'])->name('kasir.dashboard');
});

Route::middleware(['auth', 'role:koki'])->group(function () {
    Route::get('/kds/dapur', [KdsController::class, 'dapur'])->name('kds.dapur');
    Route::patch('/kds/dapur/{id}', [KdsController::class, 'ubahStatus'])->name('kds.dapur.ubah');
});

Route::middleware(['auth', 'role:barista'])->group(function () {
    Route::get('/kds/bar', [KdsController::class, 'bar'])->name('kds.bar');
    Route::patch('/kds/bar/{id}', [KdsController::class, 'ubahStatus'])->name('kds.bar.ubah');
});

Route::middleware(['auth', 'role:pelayan'])->group(function () {
    Route::get('/pelayan/panggilan', [IotController::class, 'antrean'])->name('pelayan.panggilan');
    Route::patch('/pelayan/panggilan/{id}', [IotController::class, 'ubahStatus'])->name('pelayan.panggilan.ubah');
});

require __DIR__.'/auth.php';