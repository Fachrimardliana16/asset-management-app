<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EmployeesWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pegawai per Departemen';
    protected static ?int $sort = 2;

    // Polling interval - don't auto refresh too often
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        // Cache the data for 5 minutes to reduce database queries
        $data = Cache::remember('employees_widget_data', 300, function () {
            // Query employees by department
            return Employee::query()
                ->join('master_departments', 'employees.departments_id', '=', 'master_departments.id')
                ->select('master_departments.name', DB::raw('count(*) as total'))
                ->groupBy('master_departments.id', 'master_departments.name')
                ->pluck('total', 'name')
                ->toArray();
        });

        $labels = array_keys($data);
        $values = array_values($data);

        // Generate colors
        $colors = [
            ['bg' => 'rgba(75, 192, 192, 0.2)', 'border' => 'rgba(75, 192, 192, 1)'],
            ['bg' => 'rgba(54, 162, 235, 0.2)', 'border' => 'rgba(54, 162, 235, 1)'],
            ['bg' => 'rgba(255, 206, 86, 0.2)', 'border' => 'rgba(255, 206, 86, 1)'],
            ['bg' => 'rgba(153, 102, 255, 0.2)', 'border' => 'rgba(153, 102, 255, 1)'],
            ['bg' => 'rgba(255, 159, 64, 0.2)', 'border' => 'rgba(255, 159, 64, 1)'],
        ];

        $backgroundColors = [];
        $borderColors = [];
        foreach ($labels as $index => $label) {
            $colorIndex = $index % count($colors);
            $backgroundColors[] = $colors[$colorIndex]['bg'];
            $borderColors[] = $colors[$colorIndex]['border'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pegawai',
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
