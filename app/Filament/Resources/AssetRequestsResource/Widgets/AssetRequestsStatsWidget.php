<?php

namespace App\Filament\Resources\AssetRequestsResource\Widgets;

use App\Models\AssetRequests;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetRequestsStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Total permintaan
        $totalRequests = AssetRequests::count();

        // Permintaan bulan ini
        $requestsThisMonth = AssetRequests::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->count();

        // Permintaan belum dibeli (pending atau in_progress)
        $notPurchased = AssetRequests::whereIn('purchase_status', ['pending', 'in_progress'])->count();

        // Permintaan sudah dibeli
        $purchased = AssetRequests::where('purchase_status', 'purchased')->count();

        return [
            Stat::make('Total Permintaan', $totalRequests)
                ->description('Semua permintaan barang')
                ->color('primary')
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('Permintaan Bulan Ini', $requestsThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->color('info')
                ->icon('heroicon-o-calendar'),

            Stat::make('Belum Dibeli', $notPurchased)
                ->description('Menunggu pembelian')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Sudah Dibeli', $purchased)
                ->description('Pembelian selesai')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
