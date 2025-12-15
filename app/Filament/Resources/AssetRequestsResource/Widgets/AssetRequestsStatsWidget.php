<?php

namespace App\Filament\Resources\AssetRequestsResource\Widgets;

use App\Models\AssetRequests;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AssetRequestsStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('asset_requests_dashboard_stats', 300, function () {

            $now = Carbon::now();

            $stats = AssetRequests::query()
                ->selectRaw('
                    SUM(
                        MONTH(date) = ?
                        AND YEAR(date) = ?
                    ) AS requests_this_month,

                    SUM(YEAR(date) = ?) AS requests_this_year,

                    SUM(
                        YEAR(date) = ?
                        AND purchase_status IN ("pending", "in_progress")
                    ) AS not_purchased_this_year,

                    SUM(
                        YEAR(date) = ?
                        AND purchase_status = "purchased"
                    ) AS purchased_this_year
                ', [
                    $now->month,
                    $now->year,
                    $now->year,
                    $now->year,
                    $now->year,
                ])
                ->first();

            $chart = [
                12,
                18,
                15,
                22,
                16,
                25,
                20,
                28,
                24,
                30,
                26,
                max(1, (int) $stats->requests_this_month),
            ];

            return [
                Stat::make('Permintaan Bulan Ini', (int) $stats->requests_this_month)
                    ->description($now->translatedFormat('F Y'))
                    ->chart($chart)
                    ->color('primary'),

                Stat::make('Total Permintaan Tahun Ini', (int) $stats->requests_this_year)
                    ->description('Tahun ' . $now->year)
                    ->chart($chart)
                    ->color('info'),

                Stat::make('Belum Dibeli Tahun Ini', (int) $stats->not_purchased_this_year)
                    ->description('Pending / Sedang diproses')
                    ->chart($chart)
                    ->color('warning'),

                Stat::make('Sudah Dibeli Tahun Ini', (int) $stats->purchased_this_year)
                    ->description('Pembelian selesai')
                    ->chart($chart)
                    ->color('success'),
            ];
        });
    }
}
