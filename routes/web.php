<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EMenuController;
use App\Http\Controllers\IotController;
use App\Http\Controllers\KdsController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PesananController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EMenuController::class, 'beranda'])->name('emenu.index');

Route::get('/meja/{meja}', [EMenuController::class, 'aplikasi'])->name('emenu.meja');
Route::post('/meja/{meja}/panggil', [EMenuController::class, 'panggil'])->name('emenu.panggil');
Route::post('/meja/{meja}/checkout', [EMenuController::class, 'checkout'])->name('emenu.checkout');
Route::get('/pesanan/{kode_pesanan}', [EMenuController::class, 'status'])->name('emenu.status');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/laporan', [AdminController::class, 'index'])->name('admin.laporan');

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
    Route::get('/kasir/dashboard', [PesananController::class, 'index'])->name('kasir.dashboard');
    Route::get('/kasir/pesanan/tambah', [PesananController::class, 'create'])->name('kasir.pesanan.create');
    Route::post('/kasir/pesanan', [PesananController::class, 'store'])->name('kasir.pesanan.store');
    Route::get('/kasir/pembayaran/{kode}', [PembayaranController::class, 'create'])->name('kasir.pembayaran.create');
    Route::post('/kasir/pembayaran', [PembayaranController::class, 'store'])->name('kasir.pembayaran.store');
});

Route::middleware(['auth', 'role:admin,kasir,pelayan'])->group(function () {
    Route::get('/pesanan/{kode}', [PesananController::class, 'show'])->name('pesanan.show');
    Route::patch('/pesanan/{kode}/status', [PesananController::class, 'ubahStatus'])->name('pesanan.status');
});

Route::middleware(['auth', 'role:koki'])->group(function () {
    Route::get('/kds/dapur', [KdsController::class, 'index'])->defaults('target', 'dapur')->name('kds.dapur');
    Route::patch('/kds/dapur/{id}', [KdsController::class, 'update'])->name('kds.dapur.ubah');
});

Route::middleware(['auth', 'role:barista'])->group(function () {
    Route::get('/kds/bar', [KdsController::class, 'index'])->defaults('target', 'bar')->name('kds.bar');
    Route::patch('/kds/bar/{id}', [KdsController::class, 'update'])->name('kds.bar.ubah');
});

Route::middleware(['auth', 'role:pelayan'])->group(function () {
    Route::get('/pelayan/panggilan', [IotController::class, 'antrean'])->name('pelayan.panggilan');
    Route::patch('/pelayan/panggilan/{id}', [IotController::class, 'ubahStatus'])->name('pelayan.panggilan.ubah');
});

require __DIR__.'/auth.php';