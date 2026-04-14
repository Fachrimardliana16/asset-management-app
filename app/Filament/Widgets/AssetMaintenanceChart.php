<?php

namespace App\Filament\Widgets;

use App\Models\AssetMaintenance;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AssetMaintenanceChart extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Total Pemeliharaan Barang';
    protected static ?int $sort = 4;
    protected static bool $isLazy = true;
    protected static ?string $pollingInterval = null;

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'day' => 'Hari Ini',
            'week' => 'Minggu Ini',
            'month' => 'Bulan Ini',
            'year' => 'Tahun Ini',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter;

        if ($filter === 'day') {
            return $this->getDataByDay();
        } elseif ($filter === 'week') {
            return $this->getDataByWeek();
        } elseif ($filter === 'month') {
            return $this->getDataByMonth();
        } else {
            return $this->getDataByYear();
        }
    }

    protected function getDataByDay(): array
    {
        $data = [];
        $labels = [];

        $counts = cache()->remember('chart.asset-maintenance.day.' . now()->format('Y-m-d'), 300, function () {
            return AssetMaintenance::whereDate('created_at', today())
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
                ->groupBy('hour')
                ->pluck('total', 'hour')
                ->toArray();
        });

        for ($i = 23; $i >= 0; $i--) {
            $hour = Carbon::now()->subHours($i);
            $labels[] = $hour->format('H:00');
            $data[] = $counts[$hour->hour] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemeliharaan',
                    'data' => $data,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                    'borderColor' => 'rgb(245, 158, 11)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getDataByWeek(): array
    {
        $data = [];
        $labels = [];

        $counts = cache()->remember('chart.asset-maintenance.week.' . now()->format('Y-m-d'), 300, function () {
            return AssetMaintenance::where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();
        });

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $labels[] = $day->format('D');
            $data[] = $counts[$day->toDateString()] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemeliharaan',
                    'data' => $data,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                    'borderColor' => 'rgb(245, 158, 11)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getDataByMonth(): array
    {
        $data = [];
        $labels = [];
        $daysInMonth = Carbon::now()->daysInMonth;

        $counts = cache()->remember('chart.asset-maintenance.month.' . now()->format('Y-m'), 300, function () {
            return AssetMaintenance::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->selectRaw('DAY(created_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
                ->toArray();
        });

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $labels[] = (string)$i;
            $data[] = $counts[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemeliharaan',
                    'data' => $data,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                    'borderColor' => 'rgb(245, 158, 11)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getDataByYear(): array
    {
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $counts = cache()->remember('chart.asset-maintenance.year.' . now()->format('Y'), 600, function () {
            return AssetMaintenance::whereYear('created_at', now()->year)
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();
        });

        $data = array_map(fn($i) => $counts[$i] ?? 0, range(1, 12));

        return [
            'datasets' => [
                [
                    'label' => 'Pemeliharaan',
                    'data' => $data,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                    'borderColor' => 'rgb(245, 158, 11)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
