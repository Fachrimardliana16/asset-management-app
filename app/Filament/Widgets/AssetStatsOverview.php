<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\AssetTax;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static bool $isLazy = true;
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        try {
            return cache()->remember('dashboard.asset.stats', 300, function () {
                // Optimized: Single query untuk multiple counts
                $counts = Asset::selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN condition_id IN (1, 4) THEN 1 ELSE 0 END) as good_new,
                    SUM(CASE WHEN condition_id IN (2, 3) THEN 1 ELSE 0 END) as damaged_repair
                ')->first();

                $taxesToPay = AssetTax::whereIn('payment_status', ['unpaid', 'pending'])
                    ->where('due_date', '>=', Carbon::now())
                    ->count();

                $totalAssets = $counts->total ?? 0;
                $goodNewAssets = $counts->good_new ?? 0;
                $damagedRepairAssets = $counts->damaged_repair ?? 0;

                return [
                    Stat::make('Total Aset', number_format($totalAssets))
                        ->description('Total semua aset')
                        ->descriptionIcon('heroicon-o-cube')
                        ->color('primary')
                        ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

                    Stat::make('Aset Baik & Baru', number_format($goodNewAssets))
                        ->description('Status: Baik dan Baru')
                        ->descriptionIcon('heroicon-o-check-circle')
                        ->color('success')
                        ->chart([3, 5, 6, 7, 8, 9, 10, 12]),

                    Stat::make('Aset Perbaikan & Rusak', number_format($damagedRepairAssets))
                        ->description('Status: Perbaikan dan Rusak')
                        ->descriptionIcon('heroicon-o-wrench-screwdriver')
                        ->color('danger')
                        ->chart([2, 3, 2, 4, 3, 2, 1, 2]),

                    Stat::make('Harus Bayar Pajak', number_format($taxesToPay))
                        ->description('Pajak yang harus dibayar')
                        ->descriptionIcon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->chart([1, 2, 1, 3, 2, 4, 3, 2]),
                ];
            });
        } catch (\Exception $e) {
            \Log::error('Asset Stats Widget Error: ' . $e->getMessage());
            return [
                Stat::make('Error', 'Gagal memuat data')
                    ->description('Silakan refresh halaman')
                    ->descriptionIcon('heroicon-o-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }
}
