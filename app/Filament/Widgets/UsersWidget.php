<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class UsersWidget extends BaseWidget
{
    // Disable auto-refresh polling to reduce load
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        // Cache for 5 minutes
        $TotalUser = Cache::remember('total_users_count', 300, fn() => User::count());

        return [
            Stat::make('Total User', $TotalUser)
                ->color('primary')
                ->description('Total User'),
        ];
    }
}
