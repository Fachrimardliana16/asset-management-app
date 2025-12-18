<?php

namespace App\Filament\Resources\AssetPurchaseResource\Widgets;

use App\Models\AssetPurchase;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssetPurchaseStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    // Optional: biar widget lebih lebar
    // protected int | string | array $columnSpan = 'full';

    // Tambahkan ini biar widget load setelah halaman selesai (sangat direkomendasikan!)
    protected bool $deferLoading = true;

    protected function getStats(): array
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Satu query untuk semua data
        $stats = AssetPurchase::query()
            ->selectRaw('
                COUNT(CASE WHEN MONTH(purchase_date) = ? AND YEAR(purchase_date) = ? THEN 1 END) AS count_this_month,
                COUNT(CASE WHEN YEAR(purchase_date) = ? THEN 1 END) AS count_this_year,
                COALESCE(SUM(CASE WHEN MONTH(purchase_date) = ? AND YEAR(purchase_date) = ? THEN price END), 0) AS value_this_month,
                COALESCE(SUM(CASE WHEN YEAR(purchase_date) = ? THEN price END), 0) AS value_this_year
            ', [
                $currentMonth,
                $currentYear,           // count_this_month
                $currentYear,                          // count_this_year
                $currentMonth,
                $currentYear,           // value_this_month
                $currentYear                           // value_this_year
            ])
            ->first();

        // Dummy chart (12 bulan terakhir)
        $monthlyChart = [8, 12, 10, 15, 9, 18, 14, 22, 16, 20, 18, $stats->count_this_month];

        $valueThisMonthFormatted = 'Rp ' . number_format($stats->value_this_month, 0, ',', '.');
        $valueThisYearFormatted = 'Rp ' . number_format($stats->value_this_year, 0, ',', '.');

        return [
            Stat::make('Pembelian Bulan Ini', $stats->count_this_month)
                ->description($now->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calendar', 'before')
                ->chart($monthlyChart)
                ->color('primary')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Total Pembelian Tahun Ini', $stats->count_this_year)
                ->description('Tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Nilai Pembelian Bulan Ini', $valueThisMonthFormatted)
                ->description('Pengeluaran bulan ini')
                ->descriptionIcon('heroicon-m-banknotes', 'before')
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Total Nilai Tahun Ini', $valueThisYearFormatted)
                ->description('Akumulasi pengeluaran tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-currency-dollar', 'before')
                ->chart($monthlyChart)
                ->color('warning')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
