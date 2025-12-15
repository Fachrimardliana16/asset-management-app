<?php

namespace App\Filament\Resources\AssetMutationResource\Widgets;

use App\Models\AssetMutation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AssetMutationStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('asset_mutation_dashboard_stats', 300, function () {

            $now = Carbon::now();

            /**
             * Ambil ID status transaksi (sekali join, aman)
             */
            $stats = AssetMutation::query()
                ->join(
                    'master_assets_transaction_status as ts',
                    'ts.id',
                    '=',
                    'assets_mutation.transaction_status_id'
                )
                ->selectRaw('
                    SUM(
                        MONTH(mutation_date) = ?
                        AND YEAR(mutation_date) = ?
                    ) AS mutations_this_month,

                    SUM(YEAR(mutation_date) = ?) AS mutations_this_year,

                    SUM(
                        YEAR(mutation_date) = ?
                        AND ts.name = "Transaksi Masuk"
                    ) AS incoming_this_year,

                    SUM(
                        YEAR(mutation_date) = ?
                        AND ts.name = "Transaksi Keluar"
                    ) AS outgoing_this_year
                ', [
                    $now->month,
                    $now->year,
                    $now->year,
                    $now->year,
                    $now->year,
                ])
                ->first();

            $chart = [
                8,
                12,
                10,
                15,
                9,
                14,
                11,
                18,
                13,
                16,
                12,
                max(1, (int) $stats->mutations_this_month),
            ];

            return [
                Stat::make('Mutasi Bulan Ini', (int) $stats->mutations_this_month)
                    ->description($now->translatedFormat('F Y'))
                    ->chart($chart)
                    ->color('primary')
                    ->icon('heroicon-o-arrows-right-left'),

                Stat::make('Total Mutasi Tahun Ini', (int) $stats->mutations_this_year)
                    ->description('Tahun ' . $now->year)
                    ->chart($chart)
                    ->color('info')
                    ->icon('heroicon-o-calendar-days'),

                Stat::make('Aset Masuk Tahun Ini', (int) $stats->incoming_this_year)
                    ->description('Transaksi masuk')
                    ->chart($chart)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray'),

                Stat::make('Aset Keluar Tahun Ini', (int) $stats->outgoing_this_year)
                    ->description('Transaksi keluar')
                    ->chart($chart)
                    ->color('warning')
                    ->icon('heroicon-o-arrow-up-tray'),
            ];
        });
    }
}
