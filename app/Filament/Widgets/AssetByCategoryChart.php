<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AssetByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Data Aset Berdasarkan Kategori';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '250px';
    protected static bool $isLazy = true;
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        return cache()->remember('chart.asset.by.category', 600, function () {
            try {
                $data = Asset::join('master_assets_category', 'assets.category_id', '=', 'master_assets_category.id')
                    ->select('master_assets_category.name', DB::raw('count(assets.id) as total'))
                    ->groupBy('master_assets_category.name', 'master_assets_category.id')
                    ->pluck('total', 'name')
                    ->toArray();

                if (empty($data)) {
                    $totalAssets = Asset::count();
                    $data['Belum Ada Data'] = $totalAssets > 0 ? $totalAssets : 1;
                }
            } catch (\Exception $e) {
                $data['Belum Ada Data'] = 1;
            }

            // Generate dynamic colors based on number of categories
            $colors = $this->generateColors(count($data));

            return [
                'datasets' => [
                    [
                        'label' => 'Jumlah Aset',
                        'data' => array_values($data),
                        'backgroundColor' => $colors['background'],
                        'borderColor' => $colors['border'],
                        'borderWidth' => 1,
                    ],
                ],
                'labels' => array_keys($data),
            ];
        });
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

    private function generateColors(int $count): array
    {
        $baseColors = [
            ['rgba(59, 130, 246, 0.7)', 'rgb(59, 130, 246)'],      // blue
            ['rgba(34, 197, 94, 0.7)', 'rgb(34, 197, 94)'],        // green
            ['rgba(245, 158, 11, 0.7)', 'rgb(245, 158, 11)'],      // orange
            ['rgba(239, 68, 68, 0.7)', 'rgb(239, 68, 68)'],        // red
            ['rgba(168, 85, 247, 0.7)', 'rgb(168, 85, 247)'],      // purple
            ['rgba(236, 72, 153, 0.7)', 'rgb(236, 72, 153)'],      // pink
            ['rgba(14, 165, 233, 0.7)', 'rgb(14, 165, 233)'],      // sky
            ['rgba(34, 211, 238, 0.7)', 'rgb(34, 211, 238)'],      // cyan
        ];

        $background = [];
        $border = [];

        for ($i = 0; $i < $count; $i++) {
            $colorIndex = $i % count($baseColors);
            $background[] = $baseColors[$colorIndex][0];
            $border[] = $baseColors[$colorIndex][1];
        }

        return [
            'background' => $background,
            'border' => $border,
        ];
    }
}
