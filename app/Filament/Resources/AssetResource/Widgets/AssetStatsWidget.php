<?php

namespace App\Filament\Resources\AssetResource\Widgets;

use App\Models\Asset;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AssetStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Total aset
        $totalAssets = Asset::count();

        // Total nilai aset
        $totalValue = Asset::sum('price');

        // Aset kondisi baik (join dengan master_assets_condition)
        $goodConditionCount = Asset::whereHas('condition', function ($query) {
            $query->where('name', 'Baik');
        })->count();

        // Aset kondisi rusak
        $damagedCount = Asset::whereHas('condition', function ($query) {
            $query->whereIn('name', ['Rusak', 'Perlu Perbaikan']);
        })->count();

        return [
            Stat::make('Total Aset', $totalAssets)
                ->description('Jumlah semua aset')
                ->color('primary')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Total Nilai Aset', 'Rp ' . number_format($totalValue, 0, ',', '.'))
                ->description('Akumulasi nilai aset')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Kondisi Baik', $goodConditionCount)
                ->description('Aset dalam kondisi baik')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Perlu Perhatian', $damagedCount)
                ->description('Rusak / Perlu perbaikan')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
