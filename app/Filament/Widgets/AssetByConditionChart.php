<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AssetByConditionChart extends ChartWidget
{
    protected static ?string $heading = 'Data Aset Berdasarkan Kondisi';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        try {
            $data = Asset::join('master_assets_condition', 'assets.condition_id', '=', 'master_assets_condition.id')
                ->select('master_assets_condition.name', DB::raw('count(assets.id) as total'))
                ->groupBy('master_assets_condition.name', 'master_assets_condition.id')
                ->pluck('total', 'name')
                ->toArray();

            if (empty($data)) {
                $totalAssets = Asset::count();
                $data['Belum Ada Data'] = $totalAssets > 0 ? $totalAssets : 1;
            }
        } catch (\Exception $e) {
            $data['Belum Ada Data'] = 1;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Aset',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.7)',   // Baik - green
                        'rgba(239, 68, 68, 0.7)',   // Rusak - red
                        'rgba(245, 158, 11, 0.7)',  // Perbaikan - orange
                        'rgba(59, 130, 246, 0.7)',  // Baru - blue
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                        'rgb(245, 158, 11)',
                        'rgb(59, 130, 246)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'responsive' => true,
            'cutout' => '70%',
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 15,
                        'usePointStyle' => true,
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],
            ],
        ];
    }
}
