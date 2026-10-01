<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KdsController;

Route::get('/', function () {
    return view('welcome');
})->name('emenu.index');

require __DIR__.'/auth.php';
