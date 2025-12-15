<?php

namespace App\Filament\Resources\AssetMonitoringResource\Widgets;

use App\Models\AssetMonitoring;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetMonitoringStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    // Optional: biar widget lebih lebar dan enak dilihat
    // protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Monitoring bulan ini
        $monitoringThisMonth = AssetMonitoring::whereMonth('monitoring_date', $currentMonth)
            ->whereYear('monitoring_date', $currentYear)
            ->count();

        // Monitoring tahun ini
        $monitoringThisYear = AssetMonitoring::whereYear('monitoring_date', $currentYear)
            ->count();

        // Kondisi membaik tahun ini (Baik, Sudah Diperbaiki, Baru)
        $improvedThisYear = AssetMonitoring::whereYear('monitoring_date', $currentYear)
            ->whereHas('newCondition', fn($q) => $q->whereIn('name', ['Baik', 'Sudah Diperbaiki', 'Baru']))
            ->count();

        // Kondisi memburuk tahun ini (Rusak, Perlu Perbaikan)
        $worsenedThisYear = AssetMonitoring::whereYear('monitoring_date', $currentYear)
            ->whereHas('newCondition', fn($q) => $q->whereIn('name', ['Rusak', 'Perlu Perbaikan']))
            ->count();

        // Dummy chart data (12 bulan) - bisa diganti dengan data real nanti
        $monthlyChart = [15, 18, 12, 20, 16, 22, 19, 25, 21, 23, 20, $monitoringThisMonth];

        return [
            Stat::make('Monitoring Bulan Ini', $monitoringThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('primary')
                ->icon('heroicon-o-clipboard-document-check'),

            Stat::make('Total Monitoring Tahun Ini', $monitoringThisYear)
                ->description('Tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Kondisi Membaik Tahun Ini', $improvedThisYear)
                ->description('Aset kembali baik / sudah diperbaiki')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Kondisi Memburuk Tahun Ini', $worsenedThisYear)
                ->description('Aset rusak / perlu perbaikan')
                ->descriptionIcon('heroicon-m-arrow-trending-down', 'before')
                ->chart($monthlyChart)
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
