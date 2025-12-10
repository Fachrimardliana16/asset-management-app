<?php

namespace App\Filament\Resources\AssetMonitoringResource\Pages;

use App\Filament\Resources\AssetMonitoringResource;
use App\Filament\Resources\AssetMonitoringResource\Widgets\AssetMonitoringStatsWidget;
use App\Models\MasterAssetsCondition;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListAssetMonitorings extends ListRecords
{
    protected static string $resource = AssetMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->default(now()->startOfMonth()),
                    DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->default(now()->endOfMonth()),
                    Select::make('new_condition')
                        ->label('Filter Kondisi Baru')
                        ->options(MasterAssetsCondition::pluck('name', 'id'))
                        ->placeholder('Semua Kondisi')
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                        'new_condition' => $data['new_condition'] ?? null,
                    ]));

                    return redirect()->to(route('export.asset-monitoring') . '?' . $params);
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssetMonitoringStatsWidget::class,
        ];
    }
}
