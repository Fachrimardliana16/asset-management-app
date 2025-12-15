<?php

namespace App\Filament\Resources\AssetDisposalResource\Widgets;

use App\Models\AssetDisposal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AssetDisposalStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('asset_disposal_stats', 300, function () {
            $now = Carbon::now();

            /**
             * 🔥 SATU QUERY BESAR
             */
            $stats = AssetDisposal::query()
                ->selectRaw('
                    SUM(
                        MONTH(disposal_date) = ?
                        AND YEAR(disposal_date) = ?
                    ) as disposals_this_month,

                    SUM(
                        YEAR(disposal_date) = ?
                    ) as disposals_this_year,

                    COALESCE(SUM(
                        CASE
                            WHEN YEAR(disposal_date) = ?
                            THEN book_value
                            ELSE 0
                        END
                    ), 0) as book_value_this_year,

                    COALESCE(SUM(
                        CASE
                            WHEN YEAR(disposal_date) = ?
                            THEN disposal_value
                            ELSE 0
                        END
                    ), 0) as disposal_value_this_year
                ', [
                    $now->month,
                    $now->year,
                    $now->year,
                    $now->year,
                    $now->year,
                ])
                ->first();

            /**
             * 📊 Dummy chart (biar UI hidup)
             */
            $chart = [
                1,
                3,
                2,
                4,
                2,
                5,
                3,
                4,
                6,
                3,
                4,
                max(1, (int) $stats->disposals_this_month),
            ];

            return [
                Stat::make('Penghapusan Bulan Ini', $stats->disposals_this_month)
                    ->description($now->translatedFormat('F Y'))
                    ->chart($chart)
                    ->color('primary')
                    ->icon('heroicon-o-trash'),

                Stat::make('Penghapusan Tahun Ini', $stats->disposals_this_year)
                    ->description('Tahun ' . $now->year)
                    ->chart($chart)
                    ->color('info')
                    ->icon('heroicon-o-calendar-days'),

                Stat::make(
                    'Nilai Buku Tahun Ini',
                    'Rp ' . number_format($stats->book_value_this_year, 0, ',', '.')
                )
                    ->description('Total nilai aset yang dihapus')
                    ->chart($chart)
                    ->color('warning')
                    ->icon('heroicon-o-book-open'),

                Stat::make(
                    'Pendapatan Disposal Tahun Ini',
                    'Rp ' . number_format($stats->disposal_value_this_year, 0, ',', '.')
                )
                    ->description('Total nilai jual aset')
                    ->chart($chart)
                    ->color('success')
                    ->icon('heroicon-o-banknotes'),
            ];
        });
    }
}
