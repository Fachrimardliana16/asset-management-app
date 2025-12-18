<?php

namespace App\Filament\Resources\AssetResource\Widgets;

use App\Models\Asset;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AssetStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    // Ini yang bikin dashboard langsung cepat terbuka!
    protected bool $deferLoading = true;

    // Optional: biar widget lebar full
    // protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Cache selama 15 menit (ubah jadi 5 atau 30 sesuai kebutuhan)
        // Kalau data aset jarang berubah, ini sangat efektif mengurangi beban DB
        $stats = Cache::remember('asset_stats_widget', now()->addMinutes(15), function () {
            $totalAssets = Asset::count();

            $totalBookValue = Asset::sum('book_value');

            // Eager load relasi condition biar tidak query ulang berkali-kali
            $goodCondition = Asset::with('condition')
                ->whereHas('condition', fn($q) => $q->where('name', 'Baik'))
                ->count();

            $needAttention = Asset::with('condition')
                ->whereHas('condition', fn($q) => $q->whereIn('name', ['Rusak Ringan', 'Rusak Berat', 'Perlu Perbaikan']))
                ->count();

            $nearExpiry = Asset::whereNotNull('book_value_expiry')
                ->whereBetween('book_value_expiry', [now(), now()->addYear()])
                ->count();

            $disposed = Asset::has('assetDisposals')->count();

            return compact(
                'totalAssets',
                'totalBookValue',
                'goodCondition',
                'needAttention',
                'nearExpiry',
                'disposed'
            );
        });

        // Dummy chart
        $monthlyChart = [45, 52, 48, 60, 55, 68, 62, 75, 70, 78, 72, round($stats['totalAssets'] / 10)];

        $totalBookValueFormatted = 'Rp ' . number_format($stats['totalBookValue'], 0, ',', '.');

        return [
            Stat::make('Total Aset', $stats['totalAssets'])
                ->description('Jumlah aset terdaftar')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('primary')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Total Nilai Buku', $totalBookValueFormatted)
                ->description('Akumulasi nilai buku saat ini')
                ->descriptionIcon('heroicon-m-arrow-trending-down', 'before')
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Kondisi Baik', $stats['goodCondition'])
                ->description('Aset siap pakai')
                ->descriptionIcon('heroicon-m-check-circle', 'before')
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Perlu Perhatian', $stats['needAttention'])
                ->description('Rusak atau perlu perbaikan')
                ->descriptionIcon('heroicon-m-exclamation-triangle', 'before')
                ->chart($monthlyChart)
                ->color('warning')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Mendekati Expired Buku', $stats['nearExpiry'])
                ->description('Book value expiry dalam 12 bulan')
                ->descriptionIcon('heroicon-m-clock', 'before')
                ->chart($monthlyChart)
                ->color('info')
                ->icon('heroicon-o-calendar'),

            Stat::make('Sudah Disposed', $stats['disposed'])
                ->description('Aset dihapuskan / dijual')
                ->descriptionIcon('heroicon-m-trash', 'before')
                ->chart($monthlyChart)
                ->color('danger')
                ->icon('heroicon-o-trash'),
        ];
    }
}
