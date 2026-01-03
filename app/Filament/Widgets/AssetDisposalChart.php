<?php

namespace App\Filament\Widgets;

use App\Models\AssetDisposal;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AssetDisposalChart extends ChartWidget
{
    protected static ?string $heading = 'Total Penghapusan Barang';
    protected static ?int $sort = 5;
    
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
            
            $data[] = AssetDisposal::whereBetween('created_at', [$startHour, $endHour])->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Penghapusan',
                    'data' => $data,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
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
            $data[] = AssetDisposal::whereDate('created_at', $day->toDateString())->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Penghapusan',
                    'data' => $data,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
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
            $data[] = AssetDisposal::whereDate('created_at', $date->toDateString())->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Penghapusan',
                    'data' => $data,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
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
            $data[] = AssetDisposal::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', $i)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Penghapusan',
                    'data' => $data,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
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
