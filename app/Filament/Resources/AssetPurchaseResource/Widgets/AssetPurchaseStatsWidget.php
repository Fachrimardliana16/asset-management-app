<?php

namespace App\Filament\Resources\AssetPurchaseResource\Widgets;

use App\Models\AssetPurchase;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AssetPurchaseStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('asset_purchase_dashboard_stats', 300, function () {
            $now = now();

            $stats = AssetPurchase::query()
                ->selectRaw('
                    SUM(
                        MONTH(purchase_date) = ?
                        AND YEAR(purchase_date) = ?
                    ) AS purchases_this_month,

                    SUM(YEAR(purchase_date) = ?) AS purchases_this_year,

                    SUM(
                        CASE
                            WHEN MONTH(purchase_date) = ?
                            AND YEAR(purchase_date) = ?
                            THEN price
                            ELSE 0
                        END
                    ) AS value_this_month,

                    SUM(
                        CASE
                            WHEN YEAR(purchase_date) = ?
                            THEN price
                            ELSE 0
                        END
                    ) AS value_this_year
                ', [
                    $now->month,
                    $now->year,
                    $now->year,
                    $now->month,
                    $now->year,
                    $now->year,
                ])
                ->first();

            $chart = [8, 12, 10, 15, 9, 18, 14, 22, 16, 20, 18, max(1, (int) $stats->purchases_this_month)];

            return [
                Stat::make('Pembelian Bulan Ini', (int) $stats->purchases_this_month)
                    ->description($now->translatedFormat('F Y'))
                    ->chart($chart)
                    ->color('primary')
                    ->icon('heroicon-o-shopping-bag'),

                Stat::make('Total Pembelian Tahun Ini', (int) $stats->purchases_this_year)
                    ->description('Tahun ' . $now->year)
                    ->chart($chart)
                    ->color('info')
                    ->icon('heroicon-o-calendar-days'),

                Stat::make(
                    'Nilai Pembelian Bulan Ini',
                    'Rp ' . number_format($stats->value_this_month, 0, ',', '.')
                )
                    ->description('Pengeluaran bulan ini')
                    ->chart($chart)
                    ->color('success')
                    ->icon('heroicon-o-banknotes'),

                Stat::make(
                    'Total Nilai Tahun Ini',
                    'Rp ' . number_format($stats->value_this_year, 0, ',', '.')
                )
                    ->description('Akumulasi pengeluaran')
                    ->chart($chart)
                    ->color('warning')
                    ->icon('heroicon-o-currency-dollar'),
            ];
        });
    }
}
