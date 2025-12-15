<?php

namespace App\Filament\Resources\AssetRequestsResource\Widgets;

use App\Models\AssetRequests;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetRequestsStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    // Optional: biar widget lebih lebar dan enak dilihat di dashboard
    // protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Permintaan bulan ini
        $requestsThisMonth = AssetRequests::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->count();

        // Total permintaan tahun ini
        $requestsThisYear = AssetRequests::whereYear('date', $currentYear)
            ->count();

        // Permintaan belum dibeli tahun ini (pending / in_progress)
        $notPurchasedThisYear = AssetRequests::whereYear('date', $currentYear)
            ->whereIn('purchase_status', ['pending', 'in_progress'])
            ->count();

        // Permintaan sudah dibeli tahun ini
        $purchasedThisYear = AssetRequests::whereYear('date', $currentYear)
            ->where('purchase_status', 'purchased')
            ->count();

        // Dummy chart data (12 bulan) - bisa diganti real nanti
        $monthlyChart = [12, 18, 15, 22, 16, 25, 20, 28, 24, 30, 26, $requestsThisMonth];

        return [
            Stat::make('Permintaan Bulan Ini', $requestsThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('primary')
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('Total Permintaan Tahun Ini', $requestsThisYear)
                ->description('Tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Belum Dibeli Tahun Ini', $notPurchasedThisYear)
                ->description('Pending / Sedang diproses')
                ->descriptionIcon('heroicon-m-clock', 'before')
                ->chart($monthlyChart)
                ->color('warning')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Sudah Dibeli Tahun Ini', $purchasedThisYear)
                ->description('Pembelian telah selesai')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-check-badge'),
        ];
    }
}
