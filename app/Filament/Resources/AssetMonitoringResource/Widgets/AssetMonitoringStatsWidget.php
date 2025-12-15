<?php

namespace App\Filament\Resources\AssetMonitoringResource\Widgets;

use App\Models\AssetMonitoring;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AssetMonitoringStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('asset_monitoring_dashboard_stats', 300, function () {

            $now = Carbon::now();

            /**
             * SATU QUERY AGREGASI
             */
            $stats = AssetMonitoring::query()
                ->join(
                    'master_assets_condition as c',
                    'c.id',
                    '=',
                    'assets_monitoring.new_condition_id'
                )
                ->selectRaw('
                    SUM(
                        MONTH(monitoring_date) = ?
                        AND YEAR(monitoring_date) = ?
                    ) AS monitoring_this_month,

                    SUM(YEAR(monitoring_date) = ?) AS monitoring_this_year,

                    SUM(
                        YEAR(monitoring_date) = ?
                        AND c.name IN ("Baik", "Sudah Diperbaiki", "Baru")
                    ) AS improved_this_year,

                    SUM(
                        YEAR(monitoring_date) = ?
                        AND c.name IN ("Rusak", "Perlu Perbaikan")
                    ) AS worsened_this_year
                ', [
                    $now->month,
                    $now->year,
                    $now->year,
                    $now->year,
                    $now->year,
                ])
                ->first();

            $chart = [
                15,
                18,
                12,
                20,
                16,
                22,
                19,
                25,
                21,
                23,
                20,
                max(1, (int) $stats->monitoring_this_month),
            ];

            return [
                Stat::make('Monitoring Bulan Ini', (int) $stats->monitoring_this_month)
                    ->description($now->translatedFormat('F Y'))
                    ->chart($chart)
                    ->color('primary')
                    ->icon('heroicon-o-clipboard-document-check'),

                Stat::make('Total Monitoring Tahun Ini', (int) $stats->monitoring_this_year)
                    ->description('Tahun ' . $now->year)
                    ->chart($chart)
                    ->color('info')
                    ->icon('heroicon-o-calendar-days'),

                Stat::make('Kondisi Membaik Tahun Ini', (int) $stats->improved_this_year)
                    ->description('Aset membaik / diperbaiki')
                    ->chart($chart)
                    ->color('success')
                    ->icon('heroicon-o-check-circle'),

                Stat::make('Kondisi Memburuk Tahun Ini', (int) $stats->worsened_this_year)
                    ->description('Rusak / perlu perbaikan')
                    ->chart($chart)
                    ->color('danger')
                    ->icon('heroicon-o-exclamation-triangle'),
            ];
        });
    }
}
