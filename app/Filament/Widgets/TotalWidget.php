<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class TotalWidget extends BaseWidget
{
    // Don't poll/auto-refresh - reduces load
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Cache counts for 5 minutes
        $totalAssets = Cache::remember('total_assets_count', 300, fn() => Asset::count());
        $totalEmployees = Cache::remember('total_employees_count', 300, fn() => Employee::count());

        return [
            Stat::make('Total Aset', $totalAssets)
                ->color('primary')
                ->description('Total semua Aset'),
            Stat::make('Total Pegawai', $totalEmployees)
                ->color('primary')
                ->description('Total Karyawan'),
        ];
    }
}
