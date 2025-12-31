<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Filament\Resources\AssetResource\Widgets\AssetStatsWidget;
use App\Models\Asset;
use App\Models\MasterAssetsCondition;
use App\Models\MasterAssetsLocation;
use App\Models\MasterAssetsStatus;
use App\Models\MasterAssetsSubLocation;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

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
                    Fieldset::make('Filter Export Daftar Aset')
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2, 'lg' => 3])
                                ->schema([
                                    DatePicker::make('start_date')
                                        ->label('Tanggal Pembelian Mulai')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->placeholder('Pilih tanggal')
                                        ->default(now()->startOfMonth()),

                                    DatePicker::make('end_date')
                                        ->label('Tanggal Pembelian Akhir')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->placeholder('Pilih tanggal')
                                        ->default(now()->endOfMonth()),

                                    Select::make('condition')
                                        ->label('Kondisi Aset')
                                        ->prefixIcon('heroicon-o-exclamation-triangle')
                                        ->searchable()
                                        ->options(MasterAssetsCondition::pluck('name', 'id'))
                                        ->placeholder('Semua Kondisi')
                                        ->nullable(),

                                    Select::make('status')
                                        ->label('Status Aset')
                                        ->prefixIcon('heroicon-o-information-circle')
                                        ->searchable()
                                        ->options(MasterAssetsStatus::pluck('name', 'id'))
                                        ->placeholder('Semua Status')
                                        ->nullable(),

                                    Select::make('location')
                                        ->label('Lokasi')
                                        ->prefixIcon('heroicon-o-map-pin')
                                        ->searchable()
                                        ->options(MasterAssetsLocation::pluck('name', 'id'))
                                        ->placeholder('Semua Lokasi')
                                        ->live()
                                        ->nullable(),

                                    Select::make('sub_location')
                                        ->label('Sub Lokasi')
                                        ->prefixIcon('heroicon-o-map')
                                        ->searchable()
                                        ->options(
                                            fn($get) => $get('location')
                                                ? MasterAssetsSubLocation::where('location_id', $get('location'))->pluck('name', 'id')
                                                : []
                                        )
                                        ->placeholder('Pilih lokasi dulu')
                                        ->disabled(fn($get) => !$get('location'))
                                        ->nullable(),
                                ]),
                        ])
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
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

    protected function getTableFilters(): array
    {
        return [
            Filter::make('purchase_date_range')
                ->label('Rentang Tanggal Pembelian')
                ->form([
                    Fieldset::make('Filter Tanggal')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('start_date')
                                        ->label('Dari Tanggal')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar'),

                                    DatePicker::make('end_date')
                                        ->label('Sampai Tanggal')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar'),
                                ]),
                        ])
                        ->columnSpanFull(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['start_date'], fn($q) => $q->whereDate('purchase_date', '>=', $data['start_date']))
                        ->when($data['end_date'], fn($q) => $q->whereDate('purchase_date', '<=', $data['end_date']));
                })
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['start_date'] && !$data['end_date']) return null;

                    $start = $data['start_date'] ? Carbon::parse($data['start_date'])->format('d/m/Y') : '...';
                    $end = $data['end_date'] ? Carbon::parse($data['end_date'])->format('d/m/Y') : '...';

                    return "Pembelian: {$start} → {$end}";
                }),

            SelectFilter::make('condition')
                ->label('Kondisi Aset')
                ->options(MasterAssetsCondition::pluck('name', 'id'))
                ->placeholder('Semua Kondisi'),

            SelectFilter::make('status')
                ->label('Status Aset')
                ->options(MasterAssetsStatus::pluck('name', 'id'))
                ->placeholder('Semua Status'),

            SelectFilter::make('location')
                ->label('Lokasi')
                ->options(MasterAssetsLocation::pluck('name', 'id'))
                ->placeholder('Semua Lokasi'),

            SelectFilter::make('sub_location')
                ->label('Sub Lokasi')
                ->options(MasterAssetsSubLocation::pluck('name', 'id'))
                ->placeholder('Semua Sub Lokasi'),

            Filter::make('rusak_perbaikan')
                ->label(function () {
                    $rusakConditionId = MasterAssetsCondition::where('name', 'Rusak')->first()?->id;
                    $perluPerbaikanConditionId = MasterAssetsCondition::where('name', 'Perlu Perbaikan')->first()?->id;
                    
                    $count = Asset::where(function ($q) use ($rusakConditionId, $perluPerbaikanConditionId) {
                        if ($rusakConditionId) {
                            $q->orWhere('condition_id', $rusakConditionId);
                        }
                        if ($perluPerbaikanConditionId) {
                            $q->orWhere('condition_id', $perluPerbaikanConditionId);
                        }
                    })->count();
                    
                    return "Rusak / Perbaikan ({$count})";
                })
                ->query(function (Builder $query): Builder {
                    $rusakConditionId = MasterAssetsCondition::where('name', 'Rusak')->first()?->id;
                    $perluPerbaikanConditionId = MasterAssetsCondition::where('name', 'Perlu Perbaikan')->first()?->id;
                    
                    return $query->where(function ($q) use ($rusakConditionId, $perluPerbaikanConditionId) {
                        if ($rusakConditionId) {
                            $q->orWhere('condition_id', $rusakConditionId);
                        }
                        if ($perluPerbaikanConditionId) {
                            $q->orWhere('condition_id', $perluPerbaikanConditionId);
                        }
                    });
                })
                ->indicator('Rusak / Perbaikan'),
        ];
    }
}
