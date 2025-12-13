<?php

namespace App\Http\Controllers;

use App\Models\AssetDisposal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AssetDisposalDocumentController extends Controller
{
    public function cetakSKPenghapusan($id)
    {
        // Ambil data penghapusan dengan relasi
        $disposal = AssetDisposal::with([
            'assetDisposals.assetsStatus',
            'assetDisposals.categoryAsset',
            'employeeDisposals.position',
            'employeeDisposals.department',
            'userDisposals'
        ])->findOrFail($id);

        // Data untuk dokumen
        $data = [
            'disposal' => $disposal,
            'printed_at' => now(),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('documents.disposal-sk', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('SK_Penghapusan_' . $disposal->disposals_number . '.pdf');
    }
}
