<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AssetByLocationChart extends ChartWidget
{
    protected static ?string $heading = 'Data Aset Berdasarkan Lokasi';
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        // Get assets with their current location from latest mutation
        $data = DB::table('assets')
            ->leftJoin('assets_mutation', function ($join) {
                $join->on('assets.id', '=', 'assets_mutation.assets_id')
                    ->whereRaw('assets_mutation.id = (SELECT id FROM assets_mutation WHERE assets_id = assets.id ORDER BY mutation_date DESC LIMIT 1)');
            })
            ->leftJoin('master_assets_locations', 'assets_mutation.location_id', '=', 'master_assets_locations.id')
            ->select('master_assets_locations.name', DB::raw('count(assets.id) as total'))
            ->groupBy('master_assets_locations.name')
            ->pluck('total', 'name')
            ->toArray();

        // Remove null entries
        $data = array_filter($data, function ($key) {
            return $key !== null && $key !== '';
        }, ARRAY_FILTER_USE_KEY);

        // If no data from mutations, count assets without location
        if (empty($data)) {
            $totalAssets = Asset::count();
            $data['Belum Ada Lokasi'] = $totalAssets > 0 ? $totalAssets : 1;
        }

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
