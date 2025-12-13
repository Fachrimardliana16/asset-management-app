<?php

namespace App\Http\Controllers;

use App\Models\AssetMaintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MaintenanceInvoiceController extends Controller
{
    /**
     * Generate dan cetak kwitansi pemeliharaan aset
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function printInvoice($id)
    {
        // Ambil data pemeliharaan dengan relasi ke aset
        $maintenance = AssetMaintenance::with([
            'AssetMaintenance' => function($query) {
                $query->select('id', 'assets_number', 'name');
            }
        ])->findOrFail($id);

        // Data untuk invoice
        $data = [
            'maintenance' => $maintenance,
            'printed_at' => now(),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('invoices.maintenance-invoice', $data)
            ->setPaper('a4', 'portrait');

        // Stream PDF ke browser
        $filename = 'Kwitansi_Pemeliharaan_' . 
                   ($maintenance->AssetMaintenance->assets_number ?? 'N-A') . 
                   '_' . 
                   date('Ymd', strtotime($maintenance->maintenance_date)) . 
                   '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download kwitansi pemeliharaan aset
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function downloadInvoice($id)
    {
        // Ambil data pemeliharaan dengan relasi ke aset
        $maintenance = AssetMaintenance::with([
            'AssetMaintenance' => function($query) {
                $query->select('id', 'assets_number', 'name');
            }
        ])->findOrFail($id);

        // Data untuk invoice
        $data = [
            'maintenance' => $maintenance,
            'printed_at' => now(),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('invoices.maintenance-invoice', $data)
            ->setPaper('a4', 'portrait');

        // Download PDF
        $filename = 'Kwitansi_Pemeliharaan_' . 
                   ($maintenance->AssetMaintenance->assets_number ?? 'N-A') . 
                   '_' . 
                   date('Ymd', strtotime($maintenance->maintenance_date)) . 
                   '.pdf';

        return $pdf->download($filename);
    }
}
