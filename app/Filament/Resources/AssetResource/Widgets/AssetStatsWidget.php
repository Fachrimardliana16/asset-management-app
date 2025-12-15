<?php

namespace App\Filament\Resources\AssetResource\Widgets;

use App\Models\Asset;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AssetStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $currentYear = Carbon::now()->year;

        // Total aset
        $totalAssets = Asset::count();

        // Total nilai buku saat ini
        $totalBookValue = Asset::sum('book_value');

        // Kondisi Baik
        $goodCondition = Asset::whereHas('condition', fn($q) => $q->where('name', 'Baik'))->count();

        // Kondisi perlu perhatian (Rusak / Perlu Perbaikan)
        $needAttention = Asset::whereHas('condition', fn($q) => $q->whereIn('name', ['Rusak Ringan', 'Rusak Berat', 'Perlu Perbaikan']))->count();

        // Aset mendekati akhir masa buku (dalam 12 bulan ke depan)
        $nearExpiry = Asset::whereNotNull('book_value_expiry')
            ->whereBetween('book_value_expiry', [now(), now()->addYear()])
            ->count();

        // Aset yang sudah disposed (punya record di assetDisposals)
        $disposed = Asset::has('assetDisposals')->count();

        // Dummy chart data (bisa diganti real nanti)
        $monthlyChart = [45, 52, 48, 60, 55, 68, 62, 75, 70, 78, 72, $totalAssets / 10]; // skala kecil biar chart kelihatan

        return [
            Stat::make('Total Aset', $totalAssets)
                ->description('Jumlah aset terdaftar')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('primary')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Total Nilai Buku', 'Rp ' . number_format($totalBookValue, 0, ',', '.'))
                ->description('Akumulasi nilai buku saat ini')
                ->descriptionIcon('heroicon-m-arrow-trending-down', 'before') // biasanya depreciasi
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Kondisi Baik', $goodCondition)
                ->description('Aset siap pakai')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Perlu Perhatian', $needAttention)
                ->description('Rusak atau perlu perbaikan')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($monthlyChart)
                ->color('warning')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Mendekati Expired Buku', $nearExpiry)
                ->description('Book value expiry dalam 12 bulan')
                ->descriptionIcon('heroicon-m-clock', 'before')
                ->chart($monthlyChart)
                ->color('info')
                ->icon('heroicon-o-calendar'),

            Stat::make('Sudah Disposed', $disposed)
                ->description('Aset dihapuskan / dijual')
                ->descriptionIcon('heroicon-m-arrow-trending-down', 'before')
                ->chart($monthlyChart)
                ->color('danger')
                ->icon('heroicon-o-trash'),
        ];
    }
}
