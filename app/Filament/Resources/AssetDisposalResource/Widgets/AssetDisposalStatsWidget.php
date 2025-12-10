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

        // Total penghapusan
        $totalDisposals = AssetDisposal::count();

        // Penghapusan bulan ini
        $disposalsThisMonth = AssetDisposal::whereMonth('disposal_date', $currentMonth)
            ->whereYear('disposal_date', $currentYear)
            ->count();

        // Total nilai buku yang dihapus
        $totalBookValue = AssetDisposal::sum('book_value');

        // Total nilai jual/disposal
        $totalDisposalValue = AssetDisposal::sum('disposal_value');

        return [
            Stat::make('Total Penghapusan', $totalDisposals)
                ->description('Semua aset dihapuskan')
                ->color('primary')
                ->icon('heroicon-o-trash'),

            Stat::make('Penghapusan Bulan Ini', $disposalsThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->color('info')
                ->icon('heroicon-o-calendar'),

            Stat::make('Total Nilai Buku', 'Rp ' . number_format($totalBookValue, 0, ',', '.'))
                ->description('Akumulasi nilai buku')
                ->color('warning')
                ->icon('heroicon-o-document-text'),

            Stat::make('Total Nilai Disposal', 'Rp ' . number_format($totalDisposalValue, 0, ',', '.'))
                ->description('Akumulasi nilai jual')
                ->color('success')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
