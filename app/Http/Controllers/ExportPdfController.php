<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AssetRequests;
use App\Models\AssetPurchase;
use App\Models\Asset;
use App\Models\AssetMonitoring;
use App\Models\AssetMutation;
use App\Models\AssetMaintenance;
use App\Models\AssetDisposal;

class ExportPdfController extends Controller
{
    public function assetRequests(Request $request)
    {
        $query = AssetRequests::with(['category', 'user']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $data = $query->orderBy('date', 'desc')->get();

        $pdf = Pdf::loadView('exports.asset-requests', [
            'data' => $data,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-permintaan-barang.pdf');
    }

    public function assetPurchase(Request $request)
    {
        $query = AssetPurchase::with(['category', 'condition', 'user']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('purchase_date', [$request->start_date, $request->end_date]);
        }

        $data = $query->orderBy('purchase_date', 'desc')->get();

        $pdf = Pdf::loadView('exports.asset-purchase', [
            'data' => $data,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-pembelian-barang.pdf');
    }

    public function asset(Request $request)
    {
        /**
         * ========================
         * BASE QUERY (SUMBER UTAMA)
         * ========================
         * Jangan pakai with() & orderBy di sini
         */
        $baseQuery = Asset::query();

        // ========================
        // FILTER
        // ========================
        if ($request->start_date && $request->end_date) {
            $baseQuery->whereBetween('purchase_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->condition) {
            $baseQuery->where('condition_id', $request->condition);
        }

        if ($request->status) {
            $baseQuery->where('status_id', $request->status);
        }

        if ($request->location) {
            $baseQuery->whereHas('latestMutation', function ($q) use ($request) {
                $q->where('location_id', $request->location);
            });
        }

        if ($request->sub_location) {
            $baseQuery->whereHas('latestMutation', function ($q) use ($request) {
                $q->where('sub_location_id', $request->sub_location);
            });
        }

        // ========================
        // DATA UTAMA (TABEL BESAR)
        // ========================
        $data = (clone $baseQuery)
            ->with([
                'categoryAsset',
                'conditionAsset',
                'assetsStatus',
                'latestMutation.AssetsMutationlocation',
                'latestMutation.AssetsMutationsubLocation'
            ])
            ->orderBy('purchase_date', 'desc')
            ->get();

        // ========================
        // SUMMARY PER KATEGORI
        // ========================
        $summaryCategory = (clone $baseQuery)
            ->join('master_assets_category', 'assets.category_id', '=', 'master_assets_category.id')
            ->selectRaw('master_assets_category.name AS category_name, COUNT(*) AS total')
            ->groupBy('master_assets_category.name')
            ->get();

        // ========================
        // SUMMARY PER KONDISI
        // ========================
        $summaryCondition = (clone $baseQuery)
            ->join('master_assets_condition', 'assets.condition_id', '=', 'master_assets_condition.id')
            ->selectRaw('master_assets_condition.name AS condition_name, COUNT(*) AS total')
            ->groupBy('master_assets_condition.name')
            ->get();

        // ========================
        // SUMMARY PER STATUS
        // ========================
        $summaryStatus = (clone $baseQuery)
            ->join('master_assets_status', 'assets.status_id', '=', 'master_assets_status.id')
            ->selectRaw('master_assets_status.name AS status_name, COUNT(*) AS total')
            ->groupBy('master_assets_status.name')
            ->get();

        // ========================
        // PDF
        // ========================
        $pdf = Pdf::loadView('exports.asset', [
            'data' => $data,
            'summaryCategory' => $summaryCategory,
            'summaryCondition' => $summaryCondition,
            'summaryStatus' => $summaryStatus,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'condition' => $request->condition,
            'status' => $request->status,
            'location' => $request->location,
            'sub_location' => $request->sub_location,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-data-aset.pdf');
    }



    public function assetMonitoring(Request $request)
    {
        $query = AssetMonitoring::with(['assetMonitoring', 'MonitoringNewCondition']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('monitoring_date', [$request->start_date, $request->end_date]);
        }

        if ($request->new_condition) {
            $query->where('new_condition_id', $request->new_condition);
        }

        $data = $query->orderBy('monitoring_date', 'desc')->get();

        $pdf = Pdf::loadView('exports.asset-monitoring', [
            'data' => $data,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'newCondition' => $request->new_condition,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-riwayat-monitoring.pdf');
    }

    public function assetMutation(Request $request)
    {
        $query = AssetMutation::with(['AssetsMutation', 'AssetsMutationlocation', 'AssetsMutationtransactionStatus', 'AssetsMutationemployee']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('mutation_date', [$request->start_date, $request->end_date]);
        }

        if ($request->transaction_status) {
            $query->where('transaction_status_id', $request->transaction_status);
        }

        $data = $query->orderBy('mutation_date', 'desc')->get();

        $pdf = Pdf::loadView('exports.asset-mutation', [
            'data' => $data,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'transactionStatus' => $request->transaction_status,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-mutasi-aset.pdf');
    }

    public function assetMaintenance(Request $request)
    {
        $query = AssetMaintenance::with(['AssetMaintenance']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('maintenance_date', [$request->start_date, $request->end_date]);
        }

        if ($request->service_type) {
            $query->where('service_type', $request->service_type);
        }

        $data = $query->orderBy('maintenance_date', 'desc')->get();

        $pdf = Pdf::loadView('exports.asset-maintenance', [
            'data' => $data,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'serviceType' => $request->service_type,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-pemeliharaan-aset.pdf');
    }

    public function assetDisposal(Request $request)
    {
        $query = AssetDisposal::with(['assetDisposals', 'employeeDisposals']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('disposal_date', [$request->start_date, $request->end_date]);
        }

        if ($request->disposal_process) {
            $query->where('disposal_process', $request->disposal_process);
        }

        $data = $query->orderBy('disposal_date', 'desc')->get();

        $pdf = Pdf::loadView('exports.asset-disposal', [
            'data' => $data,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'disposalProcess' => $request->disposal_process,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-penghapusan-aset.pdf');
    }
}
