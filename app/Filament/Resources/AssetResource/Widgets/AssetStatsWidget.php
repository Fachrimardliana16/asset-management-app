<?php

namespace App\Filament\Resources\AssetResource\Widgets;

use App\Models\Asset;
use App\Models\MasterAssetsCondition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AssetStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    /**
     * Ambil ID kondisi aset (dicache)
     */
    protected function getConditionIds(): array
    {
        return Cache::remember('asset_condition_ids', 3600, function () {
            return MasterAssetsCondition::pluck('id', 'name')->toArray();
        });
    }

    protected function getStats(): array
    {
        return Cache::remember('asset_dashboard_stats', 300, function () {
            $conditionIds = $this->getConditionIds();

            // Pastikan key ada biar aman
            $baik             = $conditionIds['Baik'] ?? null;
            $rusak           = $conditionIds['Rusak'] ?? 0;
            $perluPerbaikan  = $conditionIds['Perlu Perbaikan'] ?? 0;


            /**
             * SATU QUERY BESAR (hemat)
             */
            $stats = Asset::query()
                ->selectRaw('
                    COUNT(*) as total_assets,
                    COALESCE(SUM(book_value), 0) as total_book_value,
                    SUM(condition_id = ?) as good_condition,
                    SUM(condition_id IN (?, ?)) as need_attention,
                    SUM(
                        book_value_expiry IS NOT NULL
                        AND book_value_expiry BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 YEAR)
                    ) as near_expiry
                ', [
                    $baik,
                    $rusak,
                    $perluPerbaikan,
                ])
                ->first();

            /**
             * Query terpisah (lebih aman & jelas)
             */
            $disposed = Asset::has('assetDisposals')->count();

            /**
             * Chart dummy (opsional, biar UI hidup)
             */
            $chart = [
                10,
                15,
                12,
                18,
                16,
                20,
                22,
                25,
                23,
                28,
                30,
                max(1, (int) ($stats->total_assets / 10)),
            ];

            return [
                Stat::make('Total Aset', $stats->total_assets)
                    ->description('Jumlah aset terdaftar')
                    ->chart($chart)
                    ->color('primary')
                    ->icon('heroicon-o-archive-box'),

                Stat::make(
                    'Total Nilai Buku',
                    'Rp ' . number_format($stats->total_book_value, 0, ',', '.')
                )
                    ->description('Akumulasi nilai buku')
                    ->chart($chart)
                    ->color('success')
                    ->icon('heroicon-o-banknotes'),

                Stat::make('Kondisi Baik', $stats->good_condition)
                    ->description('Aset siap pakai')
                    ->chart($chart)
                    ->color('success')
                    ->icon('heroicon-o-check-circle'),

                Stat::make('Perlu Perhatian', $stats->need_attention)
                    ->description('Rusak / perlu perbaikan')
                    ->chart($chart)
                    ->color('warning')
                    ->icon('heroicon-o-exclamation-triangle'),

                Stat::make('Mendekati Expired Buku', $stats->near_expiry)
                    ->description('Dalam 12 bulan')
                    ->chart($chart)
                    ->color('info')
                    ->icon('heroicon-o-calendar'),

                Stat::make('Sudah Disposed', $disposed)
                    ->description('Dihapuskan / dijual')
                    ->chart($chart)
                    ->color('danger')
                    ->icon('heroicon-o-trash'),
            ];
        });
    }
}
