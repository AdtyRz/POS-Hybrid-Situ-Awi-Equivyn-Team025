<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\MidtransNotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EMenuController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KdsController;
use App\Http\Controllers\LaporanPenjualanController;
use App\Http\Controllers\PelayanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\RealtimeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EMenuController::class, 'beranda'])->name('emenu.index');
Route::post('/masuk', [EMenuController::class, 'masuk'])
    ->middleware('throttle:5,1')
    ->name('emenu.masuk');

Route::middleware('pesanan.no-store')->group(function () {
    Route::get('/meja/{meja}', [EMenuController::class, 'aplikasi'])
        ->middleware('throttle:120,1')
        ->name('emenu.meja');
    Route::get('/meja/{meja}/kunci', [EMenuController::class, 'kunci'])
        ->middleware('throttle:60,1')
        ->name('emenu.kunci');
    Route::post('/meja/{meja}/buka', [EMenuController::class, 'buka'])
        ->middleware('throttle:5,1')
        ->name('emenu.buka');
    Route::get('/meja/{meja}/pelacakan', [EMenuController::class, 'pelacakan'])
        ->middleware('throttle:30,1')
        ->name('emenu.pelacakan');
    Route::post('/meja/{meja}/panggil', [EMenuController::class, 'panggil'])
        ->middleware('throttle:10,1')
        ->name('emenu.panggil');
    Route::post('/meja/{meja}/checkout', [EMenuController::class, 'checkout'])
        ->middleware('throttle:20,1')
        ->name('emenu.checkout');
    Route::get('/qris/status/{kode_pesanan}', [EMenuController::class, 'qrisStatus'])
        ->middleware('throttle:60,1')
        ->name('emenu.qris.status');
    Route::get('/status/{kode_pesanan}', [EMenuController::class, 'status'])
        ->middleware('throttle:30,1')
        ->name('emenu.status');
    Route::get('/verifikasi/{kode_pesanan}', [EMenuController::class, 'verifikasi'])
        ->middleware('throttle:60,1')
        ->name('emenu.verifikasi');
    Route::get('/bayar-tunai/{kode_pesanan}', [EMenuController::class, 'bayarTunai'])
        ->middleware('throttle:30,1')
        ->name('emenu.bayar-tunai');
});

Route::get('/realtime/version', [RealtimeController::class, 'version'])
    ->middleware('throttle:120,1')
    ->name('realtime.version');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/analitik-laporan', [AdminController::class, 'dashboard'])->name('admin.analitik-laporan');
Route::get('/admin/analitik-laporan/cetak-pdf', [AdminController::class, 'cetakAnalitikPdf'])->name('admin.analitik-laporan.cetak-pdf');
Route::get('/admin/analitik-laporan/ekspor-xlsx', [AdminController::class, 'eksporAnalitikXlsx'])->name('admin.analitik-laporan.ekspor-xlsx');
    Route::get('/admin/laporan', [LaporanPenjualanController::class, 'index'])->name('admin.laporan');
    Route::get('/admin/laporan-penjualan', [LaporanPenjualanController::class, 'index'])->name('admin.laporan-penjualan');
    Route::get('/admin/laporan-penjualan/cetak', [LaporanPenjualanController::class, 'cetak'])->name('admin.laporan-penjualan.cetak');
    Route::get('/admin/laporan-penjualan/cetak-shift', [LaporanPenjualanController::class, 'cetakShift'])->name('admin.laporan-penjualan.cetak-shift');
    Route::get('/admin/laporan-penjualan/ekspor/{format}', [LaporanPenjualanController::class, 'ekspor'])
        ->whereIn('format', ['csv', 'xls'])
        ->name('admin.laporan-penjualan.ekspor');

    Route::get('/admin/pengaturan-sistem', [PengaturanController::class, 'index'])->name('admin.pengaturan-sistem');
    Route::post('/admin/pengaturan-sistem', [PengaturanController::class, 'simpan'])->name('admin.pengaturan-sistem.simpan');
    Route::post('/admin/pengaturan-sistem/logo', [PengaturanController::class, 'unggahLogo'])->name('admin.pengaturan-sistem.logo');
    Route::get('/admin/pengaturan-sistem/diagnostik', [PengaturanController::class, 'diagnostik'])->name('admin.pengaturan-sistem.diagnostik');
    Route::get('/admin/pengaturan-sistem/iot-status', [PengaturanController::class, 'statusMejaIot'])->name('admin.pengaturan-sistem.iot-status');
    Route::post('/admin/pengaturan-sistem/cache', [PengaturanController::class, 'bersihkanCache'])->name('admin.pengaturan-sistem.cache');
    Route::get('/admin/pengaturan-sistem/cadangan', [PengaturanController::class, 'unduhCadangan'])->name('admin.pengaturan-sistem.cadangan');

    Route::get('/admin/menu', [AdminController::class, 'daftarMenu'])->name('admin.menu');
    Route::get('/admin/master-menu', [AdminController::class, 'daftarMenu'])->name('admin.master-menu');
    Route::get('/admin/menu/data', [AdminController::class, 'dataMenu'])->name('admin.menu.data');
    Route::post('/admin/menu', [AdminController::class, 'simpanMenu'])->name('admin.menu.simpan');
    Route::put('/admin/menu/{id}', [AdminController::class, 'ubahMenu'])->name('admin.menu.ubah');
    Route::patch('/admin/menu/{id}/ketersediaan', [AdminController::class, 'ubahKetersediaanMenu'])->name('admin.menu.ketersediaan');
    Route::delete('/admin/menu/{id}', [AdminController::class, 'hapusMenu'])->name('admin.menu.hapus');

    Route::get('/admin/kategori', [AdminController::class, 'daftarKategori'])->name('admin.kategori');
    Route::post('/admin/kategori', [AdminController::class, 'simpanKategori'])->name('admin.kategori.simpan');

    Route::get('/admin/meja', [AdminController::class, 'daftarMeja'])->name('admin.meja');
    Route::get('/admin/master-meja', [AdminController::class, 'daftarMeja'])->name('admin.master-meja');
    Route::post('/admin/master-meja/area', [AdminController::class, 'tambahArea'])->name('admin.master-meja.tambah-area');
    Route::post('/admin/master-meja/hapus-area', [AdminController::class, 'hapusArea'])->name('admin.master-meja.hapus-area');
    Route::post('/admin/master-meja/regenerasi-token', [AdminController::class, 'regenerasiToken'])->name('admin.master-meja.regenerasi-token');
    Route::post('/admin/master-meja/panggil-pelayan', [AdminController::class, 'panggilPelayan'])->name('admin.master-meja.panggil-pelayan');
    Route::post('/admin/master-meja/selesaikan-panggilan', [AdminController::class, 'selesaikanPanggilan'])->name('admin.master-meja.selesaikan-panggilan');
    Route::post('/admin/master-meja/hapus-meja', [AdminController::class, 'hapusMeja'])->name('admin.master-meja.hapus-meja');
    Route::get('/admin/master-meja/cetak-qr', [AdminController::class, 'cetakQrMeja'])->name('admin.master-meja.cetak-qr');
    Route::post('/admin/meja', [AdminController::class, 'simpanMeja'])->name('admin.meja.simpan');

    Route::get('/admin/akun', [AdminController::class, 'daftarAkun'])->name('admin.akun');
    Route::get('/admin/manajemen-akun', [AdminController::class, 'daftarAkun'])->name('admin.manajemen-akun');
    Route::post('/admin/akun', [AdminController::class, 'simpanAkun'])->name('admin.akun.simpan');
    Route::put('/admin/akun/{id}', [AdminController::class, 'ubahAkun'])->name('admin.akun.ubah');
    Route::patch('/admin/akun/{id}/status', [AdminController::class, 'ubahStatusAkun'])->name('admin.akun.status');
    Route::delete('/admin/akun/{id}', [AdminController::class, 'hapusAkun'])->name('admin.akun.hapus');
});

Route::middleware(['auth', 'role:kasir'])->group(function () {
    Route::get('/kasir/locked', [KasirController::class, 'locked'])->name('kasir.locked');
    Route::post('/kasir/lock', [KasirController::class, 'lock'])->name('kasir.lock');
    Route::post('/kasir/unlock', [KasirController::class, 'unlock'])
        ->middleware('throttle:5,1')
        ->name('kasir.unlock');
    Route::get('/kasir/locked/notifications', [KasirController::class, 'notifikasiTerkunci'])
        ->name('kasir.locked.notifications');
});

Route::middleware(['auth', 'role:kasir', 'kasir.unlocked'])->group(function () {
    Route::get('/kasir/dashboard', [PesananController::class, 'index'])->name('kasir.dashboard');
    Route::get('/kasir/state', [PesananController::class, 'state'])->name('kasir.state');
    Route::get('/kasir/pesanan/tambah', [PesananController::class, 'create'])->name('kasir.pesanan.create');
    Route::post('/kasir/pesanan', [PesananController::class, 'store'])->name('kasir.pesanan.store');
    Route::get('/kasir/pembayaran/cari', [PembayaranController::class, 'cari'])->name('kasir.pembayaran.cari');
    Route::get('/kasir/pembayaran/{kode}', [PembayaranController::class, 'create'])->name('kasir.pembayaran.create');
    Route::post('/kasir/pembayaran', [PembayaranController::class, 'store'])->name('kasir.pembayaran.store');
    Route::post('/kasir/struk/tandai', [PembayaranController::class, 'tandaiStruk'])->name('kasir.struk.tandai');
    Route::get('/kasir/session', [KasirController::class, 'daftarMejaSession'])->name('kasir.session.index');
    Route::patch('/kasir/session/{meja}', [KasirController::class, 'akhiriSession'])->name('kasir.session.tutup');
});

Route::middleware(['auth', 'aktif', 'role:admin,kasir,pelayan', 'kasir.unlocked', 'pesanan.no-store'])->group(function () {
    Route::get('/pesanan/{kode}', [PesananController::class, 'show'])->name('pesanan.show');
    Route::patch('/pesanan/{kode}/status', [PesananController::class, 'ubahStatus'])->name('pesanan.status');
});

Route::middleware(['auth', 'role:koki'])->group(function () {
    Route::get('/kds/dapur', [KdsController::class, 'index'])->defaults('target', 'dapur')->name('kds.dapur');
    Route::patch('/kds/dapur/{id}', [KdsController::class, 'update'])->defaults('target', 'dapur')->name('kds.dapur.ubah');
});

Route::middleware(['auth', 'role:barista'])->group(function () {
    Route::get('/kds/bar', [KdsController::class, 'index'])->defaults('target', 'bar')->name('kds.bar');
    Route::patch('/kds/bar/{id}', [KdsController::class, 'update'])->defaults('target', 'bar')->name('kds.bar.ubah');
});

Route::middleware(['auth', 'role:pelayan'])->group(function () {
    Route::get('/pelayan/notifikasi-panggilan', [PelayanController::class, 'notifikasiPanggilan'])->name('pelayan.notifikasi-panggilan');
    Route::get('/pelayan/tab-antar', [PelayanController::class, 'tabAntar'])->name('pelayan.tab-antar');
    Route::get('/pelayan/tab-saung', [PelayanController::class, 'tabSaung'])->name('pelayan.tab-saung');
    Route::get('/pelayan/tab-shift', [PelayanController::class, 'tabShift'])->name('pelayan.tab-shift');
    Route::patch('/pelayan/panggilan/{id}', [PelayanController::class, 'ubahStatus'])->name('pelayan.panggilan.ubah');
    Route::post('/pelayan/pesanan', [PelayanController::class, 'simpanPesanan'])->name('pelayan.pesanan.store');
    Route::post('/pelayan/meja/{meja}/buka', [PelayanController::class, 'bukaMeja'])->name('pelayan.meja.buka');
});

require __DIR__.'/auth.php';

Route::post('/midtrans/notification', [MidtransNotificationController::class, 'handle'])->name('midtrans.notification');