<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Aplikasi ini memakai public channel agar realtime langsung jalan tanpa
| perlu konfigurasi otorisasi tambahan (cocok untuk demo/prototipe):
|
|   pesanan.{kode_pesanan}  -> status sebuah pesanan (halaman pelanggan)
|   meja.{kode_meja}        -> sesi meja dibuka/ditutup (halaman pelanggan)
|   kds.dapur, kds.bar      -> Kitchen Display System
|   kasir                   -> dashboard kasir
|   pelayan                 -> dashboard pelayan (panggilan & antar)
|   admin                   -> dashboard/laporan admin
|   menu                    -> katalog pelanggan (stok/harga menu)
|
| Private channel bawaan user tetap dipertahankan di bawah.
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
