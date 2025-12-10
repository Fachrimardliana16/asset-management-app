<?php

namespace App\Filament\Resources\AssetMonitoringResource\Widgets;

use App\Models\AssetMonitoring;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetMonitoringStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Total monitoring
        $totalMonitoring = AssetMonitoring::count();

        // Monitoring bulan ini
        $monitoringThisMonth = AssetMonitoring::whereMonth('monitoring_date', $currentMonth)
            ->whereYear('monitoring_date', $currentYear)
            ->count();

        // Kondisi membaik (new_condition = Baik atau Sudah Diperbaiki)
        $improvedCount = AssetMonitoring::whereHas('newCondition', function ($query) {
            $query->whereIn('name', ['Baik', 'Sudah Diperbaiki', 'Baru']);
        })->whereMonth('monitoring_date', $currentMonth)
            ->whereYear('monitoring_date', $currentYear)
            ->count();

        // Kondisi memburuk (new_condition = Rusak atau Perlu Perbaikan)
        $worsenedCount = AssetMonitoring::whereHas('newCondition', function ($query) {
            $query->whereIn('name', ['Rusak', 'Perlu Perbaikan']);
        })->whereMonth('monitoring_date', $currentMonth)
            ->whereYear('monitoring_date', $currentYear)
            ->count();

        return [
            Stat::make('Total Monitoring', $totalMonitoring)
                ->description('Riwayat semua monitoring')
                ->color('primary')
                ->icon('heroicon-o-clipboard-document-check'),

            Stat::make('Monitoring Bulan Ini', $monitoringThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->color('info')
                ->icon('heroicon-o-calendar'),

            Stat::make('Kondisi Membaik', $improvedCount)
                ->description('Bulan ini')
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),

            Stat::make('Kondisi Memburuk', $worsenedCount)
                ->description('Bulan ini')
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-down'),
        ];
    }
}
