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
     * Print multiple barcodes/labels at once
     */
    public function printBarcodeBulk(Request $request)
    {
        $ids = $request->input('ids');
        
        if (!$ids) {
            return redirect()->back()->with('error', 'Tidak ada asset yang dipilih');
        }

        // Convert comma-separated string to array
        $idsArray = is_array($ids) ? $ids : explode(',', $ids);
        
        // Get all assets
        $assets = Asset::with(['categoryAsset', 'conditionAsset', 'assetsStatus'])
            ->whereIn('id', $idsArray)
            ->get();

        if ($assets->isEmpty()) {
            return redirect()->back()->with('error', 'Asset tidak ditemukan');
        }

        return view('assets.print-barcode-bulk', compact('assets'));
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
