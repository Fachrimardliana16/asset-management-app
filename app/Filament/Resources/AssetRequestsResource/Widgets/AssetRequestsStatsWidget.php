<?php

namespace App\Filament\Resources\AssetRequestsResource\Widgets;

use App\Models\AssetRequests;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetRequestsStatsWidget extends BaseWidget
{
    public function getPollingInterval(): ?string
    {
        return null;
    }

    protected function getStats(): array
    {
        $now = Carbon::now();

        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth   = $now->copy()->endOfMonth();

        $startOfYear = $now->copy()->startOfYear();
        $endOfYear   = $now->copy()->endOfYear();

        // Satu query untuk semua data tahun ini + bulan ini
        $aggregates = AssetRequests::query()
            ->selectRaw('COUNT(*) as total_all')
            ->selectRaw('COUNT(CASE WHEN date >= ? AND date <= ? THEN 1 END) as total_this_month', [$startOfMonth, $endOfMonth])
            ->selectRaw('COUNT(CASE WHEN date >= ? AND date <= ? THEN 1 END) as total_this_year', [$startOfYear, $endOfYear])
            ->selectRaw('COUNT(CASE WHEN date >= ? AND date <= ? AND purchase_status IN ("pending", "in_progress") THEN 1 END) as not_purchased_this_year', [$startOfYear, $endOfYear])
            ->selectRaw('COUNT(CASE WHEN date >= ? AND date <= ? AND purchase_status = "purchased" THEN 1 END) as purchased_this_year', [$startOfYear, $endOfYear])
            ->first();

        // Dummy chart (bisa diganti real data nanti)
        $monthlyChart = [
            12,
            18,
            15,
            22,
            16,
            25,
            20,
            28,
            24,
            30,
            26,
            $aggregates->total_this_month,
        ];

        return [
            Stat::make('Permintaan Bulan Ini', $aggregates->total_this_month)
                ->description($now->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calendar', 'before')
                ->chart($monthlyChart)
                ->color('primary')
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('Total Permintaan Tahun Ini', $aggregates->total_this_year)
                ->description('Tahun ' . $now->year)
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Belum Dibeli Tahun Ini', $aggregates->not_purchased_this_year)
                ->description('Pending / Diproses')
                ->descriptionIcon('heroicon-m-clock', 'before')
                ->chart($monthlyChart)
                ->color('warning')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Sudah Dibeli Tahun Ini', $aggregates->purchased_this_year)
                ->description('Pembelian selesai')
                ->descriptionIcon('heroicon-m-check-circle', 'before')
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-check-badge'),
        ];
    }
}
