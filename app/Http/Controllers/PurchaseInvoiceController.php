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
        // Ambil data permintaan dengan relationship yang benar
        $request = AssetRequests::with([
            'items.category',
            'items.location',
            'items.subLocation',
            'department',
            'requestedBy'
        ])->findOrFail($record);

        // Ambil data pembelian dengan relationship lengkap
        $purchases = AssetPurchase::where('assetrequest_id', $record)
            ->with([
                'condition', 
                'status',
                'category',
                'location',
                'subLocation',
                'user'
            ])
            ->orderBy('item_index')
            ->get();

        if ($purchases->isEmpty()) {
            abort(404, 'Data pembelian tidak ditemukan');
        }

        // Hitung total harga dari semua pembelian
        $totalPrice = $purchases->sum('price');
        $totalQuantity = $purchases->count();

        // Data untuk invoice
        $data = [
            'request' => $request,
            'purchases' => $purchases,
            'total_price' => $totalPrice,
            'total_quantity' => $totalQuantity,
            'printed_at' => now(),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('invoices.purchase-invoice', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Faktur_Pembelian_' . $request->document_number . '.pdf');
    }
}
