<?php

namespace App\Filament\Resources\AssetMaintenanceResource\Widgets;

use App\Models\AssetMaintenance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetMaintenanceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    // Opsional: bikin widget ini span full width kalau mau lebih lebar
    // protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Jumlah pemeliharaan bulan ini
        $maintenanceThisMonth = AssetMaintenance::whereMonth('maintenance_date', $currentMonth)
            ->whereYear('maintenance_date', $currentYear)
            ->count();

        // Jumlah pemeliharaan tahun ini
        $maintenanceThisYear = AssetMaintenance::whereYear('maintenance_date', $currentYear)
            ->count();

        // Total biaya bulan ini
        $costThisMonth = AssetMaintenance::whereMonth('maintenance_date', $currentMonth)
            ->whereYear('maintenance_date', $currentYear)
            ->sum('service_cost');

        // Total biaya tahun ini
        $costThisYear = AssetMaintenance::whereYear('maintenance_date', $currentYear)
            ->sum('service_cost');

        // Dummy chart data (12 bulan terakhir, bisa diganti real nanti)
        $monthlyChart = [5, 8, 6, 12, 9, 15, 10, 18, 14, 20, 16, $maintenanceThisMonth];

        return [
            Stat::make('Pemeliharaan Bulan Ini', $maintenanceThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('primary')
                ->icon('heroicon-o-wrench-screwdriver'),

            Stat::make('Biaya Bulan Ini', 'Rp ' . number_format($costThisMonth, 0, ',', '.'))
                ->description('Pengeluaran pemeliharaan bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('warning')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Pemeliharaan Tahun Ini', $maintenanceThisYear)
                ->description('Tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-calendar'),

            Stat::make('Total Biaya Tahun Ini', 'Rp ' . number_format($costThisYear, 0, ',', '.'))
                ->description('Akumulasi biaya tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('danger')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
