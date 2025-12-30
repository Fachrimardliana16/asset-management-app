<?php

namespace App\Filament\Widgets;

use App\Models\AssetTax;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class TaxStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $upcomingCount = AssetTax::upcoming(30)->count();
        $overdueCount = AssetTax::overdue()->count();
        $pendingApprovalCount = AssetTax::pendingApproval()->count();
        $totalUnpaidAmount = AssetTax::unpaid()->sum('tax_amount');
        $totalPenaltyAmount = AssetTax::overdue()->sum('penalty_amount');
        
        return [
            Stat::make('Pajak Akan Jatuh Tempo', $upcomingCount)
                ->description('30 hari ke depan')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning')
                ->chart($this->getUpcomingTrend()),
            
            Stat::make('Pajak Terlambat', $overdueCount)
                ->description('Perlu segera dibayar')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('danger'),
            
            Stat::make('Menunggu Approval', $pendingApprovalCount)
                ->description('Perlu persetujuan')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),
            
            Stat::make('Total Belum Dibayar', 'Rp ' . Number::format($totalUnpaidAmount, locale: 'id'))
                ->description('Nilai pajak pending + overdue')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary'),
            
            Stat::make('Total Denda', 'Rp ' . Number::format($totalPenaltyAmount, locale: 'id'))
                ->description('Denda keterlambatan')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->color('danger'),
        ];
    }

    protected function getUpcomingTrend(): array
    {
        // Get tax count for the next 7 days
        $trend = [];
        for ($i = 0; $i < 7; $i++) {
            $date = now()->addDays($i);
            $count = AssetTax::whereDate('due_date', $date)
                ->whereIn('payment_status', ['pending', 'overdue'])
                ->count();
            $trend[] = $count;
        }
        return $trend;
    }
}
