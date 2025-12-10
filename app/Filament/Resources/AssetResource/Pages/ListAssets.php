<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Filament\Resources\AssetResource\Widgets\AssetStatsWidget;
use App\Models\MasterAssetsCondition;
use App\Models\MasterAssetsLocation;
use App\Models\MasterAssetsStatus;
use App\Models\MasterAssetsSubLocation;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Tanggal Pembelian Mulai')
                        ->default(now()->startOfMonth()),
                    DatePicker::make('end_date')
                        ->label('Tanggal Pembelian Akhir')
                        ->default(now()->endOfMonth()),
                    Select::make('condition')
                        ->label('Filter Kondisi')
                        ->options(MasterAssetsCondition::pluck('name', 'id'))
                        ->placeholder('Semua Kondisi')
                        ->searchable(),
                    Select::make('status')
                        ->label('Filter Status')
                        ->options(MasterAssetsStatus::pluck('name', 'id'))
                        ->placeholder('Semua Status')
                        ->searchable(),
                    Select::make('location')
                        ->label('Filter Lokasi')
                        ->options(MasterAssetsLocation::pluck('name', 'id'))
                        ->placeholder('Semua Lokasi')
                        ->searchable()
                        ->live(),
                    Select::make('sub_location')
                        ->label('Filter Sub Lokasi')
                        ->options(fn($get) => $get('location')
                            ? MasterAssetsSubLocation::where('location_id', $get('location'))->pluck('name', 'id')
                            : MasterAssetsSubLocation::pluck('name', 'id'))
                        ->placeholder('Semua Sub Lokasi')
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                        'condition' => $data['condition'] ?? null,
                        'status' => $data['status'] ?? null,
                        'location' => $data['location'] ?? null,
                        'sub_location' => $data['sub_location'] ?? null,
                    ]));

                    return redirect()->to(route('export.asset') . '?' . $params);
                }),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssetStatsWidget::class,
        ];
    }
}
