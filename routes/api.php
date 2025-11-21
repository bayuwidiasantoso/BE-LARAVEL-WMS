<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BarangApiController;
use App\Http\Controllers\Api\TransaksiBarangApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ExportApiController;
use App\Http\Controllers\Api\ImportApiController;
use App\Http\Controllers\Api\AuthApiController;

use App\Http\Controllers\Api\ActivityLogApiController;
use App\Models\ActivityLog;

// Check Healty
Route::get('/test-wms', function () {
    return response()->json(['message' => 'API WMS OK']);
});

// AUTH
Route::post('auth/login',  [AuthApiController::class, 'login']);

// Semua route di bawah ini butuh token
Route::middleware('auth:sanctum')->group(function () {

    Route::get('auth/me', [AuthApiController::class, 'me']);
    Route::post('auth/logout', [AuthApiController::class, 'logout']);

    Route::prefix('barang')->group(function () {
        // read-only: admin & staff boleh
        Route::get('/',        [BarangApiController::class, 'index'])->middleware('role:admin,staff');
        Route::get('autocomplete', [BarangApiController::class, 'autocomplete'])->middleware('role:admin,staff'); 
        Route::get('{id}',     [BarangApiController::class, 'show'])->middleware('role:admin,staff');
        Route::get('{id}/detail', [BarangApiController::class, 'detailWithHistory'])->middleware('role:admin,staff');
        
        // CRUD penuh: hanya admin
        Route::post('/',       [BarangApiController::class, 'store'])->middleware('role:admin');
        Route::put('{id}',     [BarangApiController::class, 'update'])->middleware('role:admin');
        Route::delete('{id}',  [BarangApiController::class, 'destroy'])->middleware('role:admin');
    });

    // === TRANSAKSI ===
    // Transaksi + filter + pagination
    Route::get('/transaksi',        [TransaksiBarangApiController::class, 'index'])->middleware('role:admin,staff');
    Route::post('/transaksi',       [TransaksiBarangApiController::class, 'store'])->middleware('role:admin,staff');

    // === LAPORAN STOK === (admin & staff)
    // Laporan stok rendah (di bawah threshold)
    Route::get('laporan/stok-rendah', [BarangApiController::class, 'lowStock'])->middleware('role:admin,staff');

    // === DASHBOARD === (admin & staff)
    // Dashboard
    Route::get('dashboard/summary',   [DashboardApiController::class, 'summary'])->middleware('role:admin,staff');
    Route::get('dashboard/time-series',   [DashboardApiController::class, 'timeSeries'])->middleware('role:admin,staff'); 

    // === EXPORT/IMPORT ===
    // Barang import/export → admin only
    Route::get('exports/barang',        [ExportApiController::class, 'barangExcel'])->middleware('role:admin');
    Route::get('exports/barang-pdf',    [ExportApiController::class, 'barangPdf'])->middleware('role:admin');
    Route::post('imports/barang',    [ImportApiController::class, 'importBarang'])->middleware('role:admin');
    Route::get('imports/barang/template',    [ImportApiController::class, 'templateBarang'])->middleware('role:admin');
    
    // === EXPORT/IMPORT ===    
    // Barang import/export → admin only
    Route::get('exports/transaksi',     [ExportApiController::class, 'transaksiExcel'])->middleware('role:admin,staff');
    Route::get('exports/transaksi-pdf', [ExportApiController::class, 'transaksiPdf'])->middleware('role:admin,staff');
    Route::post('imports/transaksi', [ImportApiController::class, 'importTransaksi'])->middleware('role:admin,staff');
    Route::get('imports/transaksi/template', [ImportApiController::class, 'templateTransaksi'])->middleware('role:admin,staff');

    // === ACTIVITY LOG === (admin only)
    Route::get('activity-logs', [ActivityLogApiController::class, 'index'])->middleware('role:admin');
});
