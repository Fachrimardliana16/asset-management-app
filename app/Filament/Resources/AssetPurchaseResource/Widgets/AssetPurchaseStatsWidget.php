<?php

namespace App\Filament\Resources\AssetPurchaseResource\Widgets;

use App\Models\AssetPurchase;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetPurchaseStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    // Optional: biar widget lebih lebar di dashboard
    // protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Pembelian bulan ini
        $purchasesThisMonth = AssetPurchase::whereMonth('purchase_date', $currentMonth)
            ->whereYear('purchase_date', $currentYear)
            ->count();

        // Pembelian tahun ini
        $purchasesThisYear = AssetPurchase::whereYear('purchase_date', $currentYear)
            ->count();

        // Total nilai pembelian bulan ini
        $valueThisMonth = AssetPurchase::whereMonth('purchase_date', $currentMonth)
            ->whereYear('purchase_date', $currentYear)
            ->sum('price');

        // Total nilai pembelian tahun ini
        $valueThisYear = AssetPurchase::whereYear('purchase_date', $currentYear)
            ->sum('price');

        // Dummy chart data (12 bulan) - bisa diganti real nanti jika diperlukan
        $monthlyChart = [8, 12, 10, 15, 9, 18, 14, 22, 16, 20, 18, $purchasesThisMonth];

        return [
            Stat::make('Pembelian Bulan Ini', $purchasesThisMonth)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('primary')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Total Pembelian Tahun Ini', $purchasesThisYear)
                ->description('Tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Nilai Pembelian Bulan Ini', 'Rp ' . number_format($valueThisMonth, 0, ',', '.'))
                ->description('Pengeluaran bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Total Nilai Tahun Ini', 'Rp ' . number_format($valueThisYear, 0, ',', '.'))
                ->description('Akumulasi pengeluaran tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('warning')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
