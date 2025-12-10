<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\MasterAssetsCondition;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AssetsWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Kondisi Aset';
    protected static ?int $sort = 2;
    
    // Polling interval - don't auto refresh too often
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        // Cache the data for 5 minutes to reduce database queries
        $data = Cache::remember('assets_widget_data', 300, function () {
            // Single query with join instead of multiple queries
            return Asset::query()
                ->join('master_assets_condition', 'assets.condition_id', '=', 'master_assets_condition.id')
                ->select('master_assets_condition.name', DB::raw('count(*) as total'))
                ->groupBy('master_assets_condition.id', 'master_assets_condition.name')
                ->pluck('total', 'name')
                ->toArray();
        });

        $labels = array_keys($data);
        $values = array_values($data);

        // Generate colors based on condition names
        $backgroundColors = [];
        $borderColors = [];
        foreach ($labels as $label) {
            $lower = strtolower($label);
            if (str_contains($lower, 'baik') || str_contains($lower, 'baru')) {
                $backgroundColors[] = 'rgba(75, 192, 192, 0.2)';
                $borderColors[] = 'rgba(75, 192, 192, 1)';
            } elseif (str_contains($lower, 'ringan')) {
                $backgroundColors[] = 'rgba(255, 206, 86, 0.2)';
                $borderColors[] = 'rgba(255, 206, 86, 1)';
            } elseif (str_contains($lower, 'rusak') || str_contains($lower, 'berat')) {
                $backgroundColors[] = 'rgba(255, 99, 132, 0.2)';
                $borderColors[] = 'rgba(255, 99, 132, 1)';
            } else {
                $backgroundColors[] = 'rgba(54, 162, 235, 0.2)';
                $borderColors[] = 'rgba(54, 162, 235, 1)';
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Aset',
                    'data' => $values,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
