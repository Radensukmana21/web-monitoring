<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\RegionListController;
use Illuminate\Support\Facades\Artisan;

// Route::get('/', [IndexController::class, 'index']);

Route::post('/scan-localdisk', function () {
    Artisan::call('scan:localdisk');
    return back()->with('success', 'Scan local disk berhasil dijalankan.');
})->name('scan.localdisk');
Route::post('/test-scan', [IndexController::class, 'testScan'])->name('test_scan');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [IndexController::class, 'index'] 
)->middleware('auth');

Route::get('/daftar-wilayah', [RegionListController::class, 'index'])->name('region.list');
Route::get('/daftar-wilayah/bengkulu', [RegionListController::class, 'bengkuluList'])->name('region.bengkulu');
Route::get('/daftar-wilayah/sumut', [RegionListController::class, 'sumutList'])->name('region.sumut');


Route::post('/scan-localdisk', [ScanController::class, 'run'])->name('scan.localdisk');

// Route::post('/scan-localdisk', function () {
//     Artisan::call('scan:localdisk');
//     return response()->json(['status' => 'success']);
// })->middleware('auth');