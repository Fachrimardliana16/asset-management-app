<?php

namespace App\Providers;

use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Observers
        \App\Models\AssetRequestItem::observe(\App\Observers\AssetRequestItemObserver::class);

        // Register UserTrackingObserver untuk semua models yang membutuhkan user tracking
        \App\Models\Asset::observe(\App\Observers\UserTrackingObserver::class);
        \App\Models\Employee::observe(\App\Observers\UserTrackingObserver::class);
        \App\Models\AssetTax::observe(\App\Observers\UserTrackingObserver::class);
        \App\Models\AssetMaintenance::observe(\App\Observers\UserTrackingObserver::class);
        \App\Models\AssetMutation::observe(\App\Observers\UserTrackingObserver::class);
        \App\Models\AssetMonitoring::observe(\App\Observers\UserTrackingObserver::class);
        \App\Models\AssetDisposal::observe(\App\Observers\UserTrackingObserver::class);
        \App\Models\AssetPurchase::observe(\App\Observers\UserTrackingObserver::class);

        Table::configureUsing(function (Table $table): void {
            $table
                ->emptyStateHeading('No data yet')
                ->striped()
                ->defaultPaginationPageOption(10)
                ->paginated([10, 25, 50, 100])
                ->extremePaginationLinks()
                ->defaultSort('created_at', 'desc');
        });
    }
}
