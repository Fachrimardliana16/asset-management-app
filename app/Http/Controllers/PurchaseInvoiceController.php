<?php

namespace App\Http\Controllers;

use App\Models\AssetRequests;
use App\Models\AssetPurchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PurchaseInvoiceController extends Controller
{
    public function printInvoice($record)
    {
        // Ambil data permintaan
        $request = AssetRequests::with([
            'category',
            'employee',
            'location',
            'subLocation'
        ])->findOrFail($record);

        // Ambil data pembelian
        $purchases = AssetPurchase::where('assetrequest_id', $record)
            ->with(['condition', 'status'])
            ->orderBy('item_index')
            ->get();

        if ($purchases->isEmpty()) {
            abort(404, 'Data pembelian tidak ditemukan');
        }

        $purchase = $purchases->first();

        // Data untuk invoice
        $data = [
            'request' => $request,
            'purchases' => $purchases,
            'purchase' => $purchase,
            'total_price' => $purchase->price * $request->quantity,
            'printed_at' => now(),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('invoices.purchase-invoice', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Faktur_Pembelian_' . $request->document_number . '.pdf');
    }
}
