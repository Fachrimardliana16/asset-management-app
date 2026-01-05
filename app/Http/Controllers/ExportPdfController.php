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
use App\Models\AssetTax;
use Milon\Barcode\Facades\DNS2DFacade as DNS2D;

class ExportPdfController extends Controller
{
    public function assetRequests(Request $request)
    {
        try {
            $query = AssetRequests::with(['category', 'user']);

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }

            $data = $query->orderBy('date', 'desc')->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'error' => 'Tidak ada data untuk diekspor'
                ], 404);
            }

            $pdf = Pdf::loadView('exports.asset-requests', [
                'data' => $data,
                'startDate' => $request->start_date,
                'endDate' => $request->end_date,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('laporan-permintaan-barang.pdf');
        } catch (\Exception $e) {
            \Log::error('Export Asset Requests PDF Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengekspor data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assetPurchase(Request $request)
    {
        try {
            $query = AssetPurchase::with(['category', 'condition', 'user']);

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('purchase_date', [$request->start_date, $request->end_date]);
            }

            $data = $query->orderBy('purchase_date', 'desc')->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'error' => 'Tidak ada data untuk diekspor'
                ], 404);
            }

            $pdf = Pdf::loadView('exports.asset-purchase', [
                'data' => $data,
                'startDate' => $request->start_date,
                'endDate' => $request->end_date,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('laporan-pembelian-barang.pdf');
        } catch (\Exception $e) {
            \Log::error('Export Asset Purchase PDF Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengekspor data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function asset(Request $request)
    {
        try {
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

            if ($data->isEmpty()) {
                return response()->json([
                    'error' => 'Tidak ada data aset untuk diekspor'
                ], 404);
            }

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
        } catch (\Exception $e) {
            \Log::error('Export Asset PDF Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengekspor data aset: ' . $e->getMessage()
            ], 500);
        }
    }



    public function assetMonitoring(Request $request)
    {
        try {
            $query = AssetMonitoring::with(['assetMonitoring', 'MonitoringNewCondition']);

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('monitoring_date', [$request->start_date, $request->end_date]);
            }

            if ($request->new_condition) {
                $query->where('new_condition_id', $request->new_condition);
            }

            $data = $query->orderBy('monitoring_date', 'desc')->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'error' => 'Tidak ada data monitoring untuk diekspor'
                ], 404);
            }

            $pdf = Pdf::loadView('exports.asset-monitoring', [
                'data' => $data,
                'startDate' => $request->start_date,
                'endDate' => $request->end_date,
                'newCondition' => $request->new_condition,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('laporan-riwayat-monitoring.pdf');
        } catch (\Exception $e) {
            \Log::error('Export Asset Monitoring PDF Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengekspor data monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assetMutation(Request $request)
    {
        try {
            $query = AssetMutation::with(['AssetsMutation', 'AssetsMutationlocation', 'AssetsMutationtransactionStatus', 'AssetsMutationemployee']);

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('mutation_date', [$request->start_date, $request->end_date]);
            }

            if ($request->transaction_status) {
                $query->where('transaction_status_id', $request->transaction_status);
            }

            $data = $query->orderBy('mutation_date', 'desc')->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'error' => 'Tidak ada data mutasi untuk diekspor'
                ], 404);
            }

            $pdf = Pdf::loadView('exports.asset-mutation', [
                'data' => $data,
                'startDate' => $request->start_date,
                'endDate' => $request->end_date,
                'transactionStatus' => $request->transaction_status,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('laporan-mutasi-aset.pdf');
        } catch (\Exception $e) {
            \Log::error('Export Asset Mutation PDF Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengekspor data mutasi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assetMaintenance(Request $request)
    {
        try {
            $query = AssetMaintenance::with(['AssetMaintenance']);

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('maintenance_date', [$request->start_date, $request->end_date]);
            }

            if ($request->service_type) {
                $query->where('service_type', $request->service_type);
            }

            $data = $query->orderBy('maintenance_date', 'desc')->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'error' => 'Tidak ada data pemeliharaan untuk diekspor'
                ], 404);
            }

            $pdf = Pdf::loadView('exports.asset-maintenance', [
                'data' => $data,
                'startDate' => $request->start_date,
                'endDate' => $request->end_date,
                'serviceType' => $request->service_type,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('laporan-pemeliharaan-aset.pdf');
        } catch (\Exception $e) {
            \Log::error('Export Asset Maintenance PDF Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengekspor data pemeliharaan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assetDisposal(Request $request)
    {
        try {
            $query = AssetDisposal::with(['assetDisposals', 'employeeDisposals']);

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('disposal_date', [$request->start_date, $request->end_date]);
            }

            if ($request->disposal_process) {
                $query->where('disposal_process', $request->disposal_process);
            }

            $data = $query->orderBy('disposal_date', 'desc')->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'error' => 'Tidak ada data penghapusan untuk diekspor'
                ], 404);
            }

            $pdf = Pdf::loadView('exports.asset-disposal', [
                'data' => $data,
                'startDate' => $request->start_date,
                'endDate' => $request->end_date,
                'disposalProcess' => $request->disposal_process,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('laporan-penghapusan-aset.pdf');
        } catch (\Exception $e) {
            \Log::error('Export Asset Disposal PDF Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengekspor data penghapusan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assetTax(Request $request)
    {
        try {
            $query = AssetTax::with(['asset', 'taxType']);

            // Filter by tax type
            if ($request->tax_type_id) {
                $query->where('tax_type_id', $request->tax_type_id);
            }

            // Filter by tax year
            if ($request->tax_year) {
                $query->where('tax_year', $request->tax_year);
            }

            // Filter by payment status
            if ($request->payment_status) {
                $query->where('payment_status', $request->payment_status);
            }

            // Filter by due date range
            if ($request->due_date_start && $request->due_date_end) {
                $query->whereBetween('due_date', [$request->due_date_start, $request->due_date_end]);
            } elseif ($request->due_date_start) {
                $query->whereDate('due_date', '>=', $request->due_date_start);
            } elseif ($request->due_date_end) {
                $query->whereDate('due_date', '<=', $request->due_date_end);
            }

            // Filter by payment date range
            if ($request->payment_date_start && $request->payment_date_end) {
                $query->whereBetween('payment_date', [$request->payment_date_start, $request->payment_date_end]);
            } elseif ($request->payment_date_start) {
                $query->whereDate('payment_date', '>=', $request->payment_date_start);
            } elseif ($request->payment_date_end) {
                $query->whereDate('payment_date', '<=', $request->payment_date_end);
            }

            $data = $query->orderBy('due_date', 'desc')->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'error' => 'Tidak ada data pajak untuk diekspor'
                ], 404);
            }

            $pdf = Pdf::loadView('exports.asset-tax', [
                'data' => $data,
                'filters' => [
                    'tax_type' => $request->tax_type_id ? \App\Models\MasterTaxType::find($request->tax_type_id)?->name : 'Semua',
                    'tax_year' => $request->tax_year ?? 'Semua',
                    'payment_status' => $request->payment_status ?? 'Semua',
                    'due_date_start' => $request->due_date_start,
                    'due_date_end' => $request->due_date_end,
                    'payment_date_start' => $request->payment_date_start,
                    'payment_date_end' => $request->payment_date_end,
                ],
            ])->setPaper('a4', 'landscape');

            return $pdf->download('laporan-pajak-aset-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Export Asset Tax PDF Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengekspor data pajak: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assetProfile(Request $request, $id)
    {
        try {
            // Parse selected sections first
            $selectedSections = explode(',', $request->get('sections', 'asset_info'));

            // Build query with conditional eager loading based on selected sections
            $query = Asset::query();

            // Always load basic info
            if (in_array('asset_info', $selectedSections) || in_array('financial_info', $selectedSections)) {
                $query->with(['categoryAsset', 'assetsStatus', 'conditionAsset', 'AssetTransactionStatus']);
            }

            // Conditionally load relations only if section is selected
            if (in_array('mutations', $selectedSections)) {
                $query->with([
                    'latestMutation.AssetsMutationtransactionStatus',
                    'latestMutation.AssetsMutationemployee',
                    'latestMutation.AssetsMutationlocation',
                    'AssetsMutation' => function ($q) {
                        $q->with(['AssetsMutationtransactionStatus', 'AssetsMutationemployee', 'AssetsMutationlocation'])
                            ->latest()->limit(50); // Limit to last 50 mutations
                    }
                ]);
            }

            if (in_array('monitoring', $selectedSections)) {
                $query->with(['assetMonitoring' => function ($q) {
                    $q->with(['MonitoringoldCondition', 'MonitoringNewCondition', 'user'])
                        ->latest()->limit(30); // Limit to last 30 monitoring records
                }]);
            }

            if (in_array('maintenance', $selectedSections)) {
                $query->with(['AssetMaintenance' => function ($q) {
                    $q->latest()->limit(30); // Limit to last 30 maintenance records
                }]);
            }

            if (in_array('taxes', $selectedSections)) {
                $query->with(['taxes' => function ($q) {
                    $q->with('taxType')->latest();
                }]);
            }

            $asset = $query->findOrFail($id);

            // Generate QR Code
            $qrCode = null;
            if (in_array('qr_code', $selectedSections)) {
                $qrCode = base64_encode(DNS2D::getBarcodeSVG($asset->assets_number, 'QRCODE', 5, 5));
            }

            $pdf = Pdf::loadView('exports.asset-profile', [
                'asset' => $asset,
                'selectedSections' => $selectedSections,
                'qrCode' => $qrCode,
            ])->setPaper('a4', 'portrait');

            return $pdf->download('profil-aset-' . $asset->assets_number . '-' . date('Y-m-d') . '.pdf');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Asset Profile Not Found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Aset tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Export Asset Profile PDF Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Gagal mengekspor profil aset: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
}
