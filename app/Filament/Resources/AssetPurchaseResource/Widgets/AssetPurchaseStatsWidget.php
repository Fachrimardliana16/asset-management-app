<?php

namespace App\Filament\Resources\AssetPurchaseResource\Widgets;

use App\Models\AssetPurchase;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetPurchaseStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Total pembelian
        $totalPurchases = AssetPurchase::count();

        // Pembelian bulan ini
        $purchasesThisMonth = AssetPurchase::whereMonth('purchase_date', $currentMonth)
            ->whereYear('purchase_date', $currentYear)
            ->count();

        // Total nilai pembelian bulan ini
        $totalValueThisMonth = AssetPurchase::whereMonth('purchase_date', $currentMonth)
            ->whereYear('purchase_date', $currentYear)
            ->sum('price');

        // Total nilai semua pembelian
        $totalValue = AssetPurchase::sum('price');

        return [
            Stat::make('Total Pembelian', $totalPurchases)
                ->description('Semua pembelian barang')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Pembelian Bulan Ini', $purchasesThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->color('info')
                ->icon('heroicon-o-calendar'),

            Stat::make('Nilai Bulan Ini', 'Rp ' . number_format($totalValueThisMonth, 0, ',', '.'))
                ->description('Total nilai pembelian')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Total Nilai', 'Rp ' . number_format($totalValue, 0, ',', '.'))
                ->description('Akumulasi semua pembelian')
                ->color('warning')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
