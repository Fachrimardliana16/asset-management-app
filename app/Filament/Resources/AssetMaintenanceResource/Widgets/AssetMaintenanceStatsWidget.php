<?php

namespace App\Filament\Resources\AssetMaintenanceResource\Widgets;

use App\Models\AssetMaintenance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetMaintenanceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Total pemeliharaan
        $totalMaintenance = AssetMaintenance::count();

        // Pemeliharaan bulan ini
        $maintenanceThisMonth = AssetMaintenance::whereMonth('maintenance_date', $currentMonth)
            ->whereYear('maintenance_date', $currentYear)
            ->count();

        // Total biaya bulan ini
        $costThisMonth = AssetMaintenance::whereMonth('maintenance_date', $currentMonth)
            ->whereYear('maintenance_date', $currentYear)
            ->sum('service_cost');

        // Total biaya keseluruhan
        $totalCost = AssetMaintenance::sum('service_cost');

        return [
            Stat::make('Total Pemeliharaan', $totalMaintenance)
                ->description('Semua riwayat pemeliharaan')
                ->color('primary')
                ->icon('heroicon-o-wrench-screwdriver'),

            Stat::make('Pemeliharaan Bulan Ini', $maintenanceThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->color('info')
                ->icon('heroicon-o-calendar'),

            Stat::make('Biaya Bulan Ini', 'Rp ' . number_format($costThisMonth, 0, ',', '.'))
                ->description('Total biaya pemeliharaan')
                ->color('warning')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Total Biaya', 'Rp ' . number_format($totalCost, 0, ',', '.'))
                ->description('Akumulasi biaya pemeliharaan')
                ->color('danger')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
