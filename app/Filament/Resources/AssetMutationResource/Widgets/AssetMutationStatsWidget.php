<?php

namespace App\Filament\Resources\AssetMutationResource\Widgets;

use App\Models\AssetMutation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetMutationStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Total mutasi
        $totalMutations = AssetMutation::count();

        // Mutasi bulan ini
        $mutationsThisMonth = AssetMutation::whereMonth('mutation_date', $currentMonth)
            ->whereYear('mutation_date', $currentYear)
            ->count();

        // Mutasi masuk (Transaksi Masuk)
        $incomingMutations = AssetMutation::whereHas('transactionStatus', function ($query) {
            $query->where('name', 'Transaksi Masuk');
        })->count();

        // Mutasi keluar (Transaksi Keluar)
        $outgoingMutations = AssetMutation::whereHas('transactionStatus', function ($query) {
            $query->where('name', 'Transaksi Keluar');
        })->count();

        return [
            Stat::make('Total Mutasi', $totalMutations)
                ->description('Semua mutasi barang')
                ->color('primary')
                ->icon('heroicon-o-arrows-right-left'),

            Stat::make('Mutasi Bulan Ini', $mutationsThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->color('info')
                ->icon('heroicon-o-calendar'),

            Stat::make('Transaksi Masuk', $incomingMutations)
                ->description('Total aset masuk')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),

            Stat::make('Transaksi Keluar', $outgoingMutations)
                ->description('Total aset keluar')
                ->color('warning')
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }
}
