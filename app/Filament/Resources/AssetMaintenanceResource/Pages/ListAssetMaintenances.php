<?php

namespace App\Filament\Resources\AssetMaintenanceResource\Pages;

use App\Filament\Resources\AssetMaintenanceResource;
use App\Filament\Resources\AssetMaintenanceResource\Widgets\AssetMaintenanceStatsWidget;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListAssetMaintenances extends ListRecords
{
    protected static string $resource = AssetMaintenanceResource::class;

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
                    Select::make('service_type')
                        ->label('Filter Jenis Perbaikan')
                        ->options([
                            'Perbaikan Ringan' => 'Perbaikan Ringan',
                            'Perbaikan Sedang' => 'Perbaikan Sedang',
                            'Perbaikan Berat' => 'Perbaikan Berat',
                            'Perawatan Berkala' => 'Perawatan Berkala',
                        ])
                        ->placeholder('Semua Jenis'),
                ])
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                        'service_type' => $data['service_type'] ?? null,
                    ]));

                    return redirect()->to(route('export.asset-maintenance') . '?' . $params);
                }),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssetMaintenanceStatsWidget::class,
        ];
    }
}
