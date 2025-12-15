<?php

namespace App\Filament\Resources\AssetMutationResource\Widgets;

use App\Models\AssetMutation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetMutationStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    // Optional: biar widget lebih lebar dan enak dilihat
    // protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Mutasi bulan ini
        $mutationsThisMonth = AssetMutation::whereMonth('mutation_date', $currentMonth)
            ->whereYear('mutation_date', $currentYear)
            ->count();

        // Mutasi tahun ini
        $mutationsThisYear = AssetMutation::whereYear('mutation_date', $currentYear)
            ->count();

        // Transaksi Masuk tahun ini
        $incomingThisYear = AssetMutation::whereYear('mutation_date', $currentYear)
            ->whereHas('transactionStatus', fn($q) => $q->where('name', 'Transaksi Masuk'))
            ->count();

        // Transaksi Keluar tahun ini
        $outgoingThisYear = AssetMutation::whereYear('mutation_date', $currentYear)
            ->whereHas('transactionStatus', fn($q) => $q->where('name', 'Transaksi Keluar'))
            ->count();

        // Dummy chart data (12 bulan) - bisa diganti real nanti kalau mau
        $monthlyChart = [8, 12, 10, 15, 9, 14, 11, 18, 13, 16, 12, $mutationsThisMonth];

        return [
            Stat::make('Mutasi Bulan Ini', $mutationsThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('primary')
                ->icon('heroicon-o-arrows-right-left'),

            Stat::make('Total Mutasi Tahun Ini', $mutationsThisYear)
                ->description('Tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Aset Masuk Tahun Ini', $incomingThisYear)
                ->description('Transaksi masuk berhasil')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),

            Stat::make('Aset Keluar Tahun Ini', $outgoingThisYear)
                ->description('Transaksi keluar berhasil')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('warning')
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }
}
