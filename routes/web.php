<?php

use App\Http\Controllers\AssetBarcodeController;
use App\Http\Controllers\ExportPdfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Root URL ditangani oleh Filament Panel (path='')
// Tidak perlu route '/' karena Filament sudah handle

// Asset Routes
Route::middleware(['auth'])->group(function () {
    // Print barcode/QR stiker
    Route::get('/asset/{id}/print-barcode', [AssetBarcodeController::class, 'printBarcode'])
        ->name('asset.print-barcode');

    // Export PDF Routes
    Route::get('/export/asset-requests', [ExportPdfController::class, 'assetRequests'])
        ->name('export.asset-requests');
    Route::get('/export/asset-purchase', [ExportPdfController::class, 'assetPurchase'])
        ->name('export.asset-purchase');
    Route::get('/export/asset', [ExportPdfController::class, 'asset'])
        ->name('export.asset');
    Route::get('/export/asset-monitoring', [ExportPdfController::class, 'assetMonitoring'])
        ->name('export.asset-monitoring');
    Route::get('/export/asset-mutation', [ExportPdfController::class, 'assetMutation'])
        ->name('export.asset-mutation');
    Route::get('/export/asset-maintenance', [ExportPdfController::class, 'assetMaintenance'])
        ->name('export.asset-maintenance');
    Route::get('/export/asset-disposal', [ExportPdfController::class, 'assetDisposal'])
        ->name('export.asset-disposal');
});

// QR Code Scan Route - redirect ke monitoring (dengan login required)
Route::get('/asset/scan/{id}', [AssetBarcodeController::class, 'scanRedirect'])
    ->name('asset.scan');
