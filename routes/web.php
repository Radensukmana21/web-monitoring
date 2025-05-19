<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\RegionListController;
use Illuminate\Support\Facades\Artisan;

// Route::get('/', [IndexController::class, 'index']);

Route::post('/scan-localdisk', function () {
    Artisan::call('scan:localdisk');
    return back()->with('success', 'Scan local disk berhasil dijalankan.');
})->name('scan.localdisk');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [IndexController::class, 'index'] 
)->middleware('auth');
Route::get('/daftar-wilayah', [RegionListController::class, 'index'])->name('region.list');