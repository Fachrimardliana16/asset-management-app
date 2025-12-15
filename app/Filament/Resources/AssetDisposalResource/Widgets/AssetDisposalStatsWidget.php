<?php

namespace App\Filament\Resources\AssetDisposalResource\Widgets;

use App\Models\AssetDisposal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetDisposalStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Penghapusan bulan ini
        $disposalsThisMonth = AssetDisposal::whereMonth('disposal_date', $currentMonth)
            ->whereYear('disposal_date', $currentYear)
            ->count();

        // Penghapusan tahun ini
        $disposalsThisYear = AssetDisposal::whereYear('disposal_date', $currentYear)
            ->count();

        // Total nilai buku tahun ini
        $bookValueThisYear = AssetDisposal::whereYear('disposal_date', $currentYear)
            ->sum('book_value');

        // Total nilai disposal tahun ini
        $disposalValueThisYear = AssetDisposal::whereYear('disposal_date', $currentYear)
            ->sum('disposal_value');

        // Dummy chart data (12 bulan terakhir) - bisa diganti real nanti
        $monthlyChart = [1, 3, 2, 4, 2, 5, 3, 4, 6, 3, 4, $disposalsThisMonth];

        return [
            Stat::make('Penghapusan Bulan Ini', $disposalsThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('primary')
                ->icon('heroicon-o-trash'),

            Stat::make('Penghapusan Tahun Ini', $disposalsThisYear)
                ->description('Tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Nilai Buku Tahun Ini', 'Rp ' . number_format($bookValueThisYear, 0, ',', '.'))
                ->description('Total nilai aset yang dihapus tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-down', 'before')
                ->chart($monthlyChart)
                ->color('warning')
                ->icon('heroicon-o-book-open'),

            Stat::make('Pendapatan Disposal Tahun Ini', 'Rp ' . number_format($disposalValueThisYear, 0, ',', '.'))
                ->description('Total nilai jual/penghapusan tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
