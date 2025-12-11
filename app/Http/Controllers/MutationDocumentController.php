<?php

namespace App\Http\Controllers;

use App\Models\AssetMutation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MutationDocumentController extends Controller
{
    public function cetakSerahTerima($id)
    {
        // Ambil data mutasi dengan relasi
        $mutation = AssetMutation::with([
            'AssetsMutation',
            'AssetsMutationemployee.position',
            'AssetsMutationemployee.department',
            'AssetsMutationlocation',
            'AssetsMutationsubLocation',
            'AssetsMutationtransactionStatus',
            'MutationCondition'
        ])->findOrFail($id);

        // Tentukan tipe transaksi
        $transactionType = $mutation->AssetsMutationtransactionStatus->name;
        $isMutasiKeluar = $transactionType === 'Transaksi Keluar';

        // Data untuk dokumen
        $data = [
            'mutation' => $mutation,
            'transactionType' => $transactionType,
            'isMutasiKeluar' => $isMutasiKeluar,
            'printed_at' => now(),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('documents.mutation-serah-terima', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Serah_Terima_Mutasi_' . $mutation->mutations_number . '.pdf');
    }
}
