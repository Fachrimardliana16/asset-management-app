<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetBarcodeController extends Controller
{
    public function printBarcode($id)
    {
        $asset = Asset::with(['categoryAsset', 'conditionAsset', 'assetsStatus'])->findOrFail($id);
        
        return view('assets.print-barcode', compact('asset'));
    }

    /**
     * Handle QR Code scan - redirect ke halaman monitoring
     * Jika belum login, akan redirect ke login dulu
     */
    public function scanRedirect($id)
    {
        $asset = Asset::find($id);
        
        if (!$asset) {
            // Jika aset tidak ditemukan, redirect ke halaman error
            return redirect()->route('filament.admin.pages.monitoring-aset-scanner')
                ->with('error', 'Aset tidak ditemukan');
        }

        // Redirect ke halaman monitoring dengan parameter asset
        // Filament akan handle authentication
        return redirect()->route('filament.admin.pages.monitoring-aset-scanner', [
            'asset' => $asset->assets_number
        ]);
    }
}
