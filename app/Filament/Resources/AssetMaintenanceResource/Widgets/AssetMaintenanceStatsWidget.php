<?php

namespace App\Filament\Resources\AssetMaintenanceResource\Widgets;

use App\Models\AssetMaintenance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AssetMaintenanceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('asset_maintenance_stats', 300, function () {
            $now = Carbon::now();

            /**
             * SATU QUERY BESAR 🔥
             */
            $stats = AssetMaintenance::query()
                ->selectRaw('
                    SUM(MONTH(maintenance_date) = ? AND YEAR(maintenance_date) = ?) as maintenance_this_month,
                    SUM(YEAR(maintenance_date) = ?) as maintenance_this_year,
                    COALESCE(SUM(
                        CASE
                            WHEN MONTH(maintenance_date) = ? AND YEAR(maintenance_date) = ?
                            THEN service_cost
                            ELSE 0
                        END
                    ), 0) as cost_this_month,
                    COALESCE(SUM(
                        CASE
                            WHEN YEAR(maintenance_date) = ?
                            THEN service_cost
                            ELSE 0
                        END
                    ), 0) as cost_this_year
                ', [
                    $now->month,
                    $now->year,
                    $now->year,
                    $now->month,
                    $now->year,
                    $now->year,
                ])
                ->first();

            /**
             * Chart dummy (biar UI hidup)
             */
            $chart = [
                5,
                8,
                6,
                12,
                9,
                15,
                10,
                18,
                14,
                20,
                16,
                max(1, (int) $stats->maintenance_this_month),
            ];

            return [
                Stat::make('Pemeliharaan Bulan Ini', $stats->maintenance_this_month)
                    ->description($now->translatedFormat('F Y'))
                    ->chart($chart)
                    ->color('primary')
                    ->icon('heroicon-o-wrench-screwdriver'),

                Stat::make(
                    'Biaya Bulan Ini',
                    'Rp ' . number_format($stats->cost_this_month, 0, ',', '.')
                )
                    ->description('Pengeluaran pemeliharaan bulan ini')
                    ->chart($chart)
                    ->color('warning')
                    ->icon('heroicon-o-banknotes'),

                Stat::make('Pemeliharaan Tahun Ini', $stats->maintenance_this_year)
                    ->description('Tahun ' . $now->year)
                    ->chart($chart)
                    ->color('success')
                    ->icon('heroicon-o-calendar'),

                Stat::make(
                    'Total Biaya Tahun Ini',
                    'Rp ' . number_format($stats->cost_this_year, 0, ',', '.')
                )
                    ->description('Akumulasi biaya tahun ini')
                    ->chart($chart)
                    ->color('danger')
                    ->icon('heroicon-o-currency-dollar'),
            ];
        });
    }
}
