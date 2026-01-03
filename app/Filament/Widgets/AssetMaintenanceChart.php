<?php

namespace App\Filament\Widgets;

use App\Models\AssetMaintenance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AssetMaintenanceChart extends ChartWidget
{
    protected static ?string $heading = 'Total Pemeliharaan Barang';
    protected static ?int $sort = 4;

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

        for ($i = 23; $i >= 0; $i--) {
            $hour = Carbon::now()->subHours($i);
            $labels[] = $hour->format('H:00');

            $startHour = Carbon::today()->addHours($hour->hour);
            $endHour = $startHour->copy()->addHour();

            $data[] = AssetMaintenance::whereBetween('created_at', [$startHour, $endHour])->count();
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

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $labels[] = $day->format('D');
            $data[] = AssetMaintenance::whereDate('created_at', $day->toDateString())->count();
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

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $labels[] = (string)$i;
            $date = Carbon::now()->startOfMonth()->addDays($i - 1);
            $data[] = AssetMaintenance::whereDate('created_at', $date->toDateString())->count();
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
        $data = [];
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        for ($i = 1; $i <= 12; $i++) {
            $data[] = AssetMaintenance::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', $i)
                ->count();
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

    protected function getType(): string
    {
        return 'line';
    }
}
