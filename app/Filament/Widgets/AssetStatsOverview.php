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

    protected function getStats(): array
    {
        // Total aset
        $totalAssets = Asset::count();

        // Total aset dengan status baik dan baru (condition_id = 1 untuk Baik, 4 untuk Baru)
        $goodNewAssets = Asset::whereIn('condition_id', [1, 4])->count();

        // Total aset dengan status perbaikan dan rusak (condition_id = 2 untuk Rusak, 3 untuk Perbaikan)
        $damagedRepairAssets = Asset::whereIn('condition_id', [2, 3])->count();

        // Harus bayar pajak (status unpaid atau pending)
        $taxesToPay = AssetTax::whereIn('payment_status', ['unpaid', 'pending'])
            ->where('due_date', '>=', Carbon::now())
            ->count();

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
    }
}
