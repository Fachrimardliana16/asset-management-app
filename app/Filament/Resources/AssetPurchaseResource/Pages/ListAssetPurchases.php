<?php

namespace App\Filament\Resources\AssetPurchaseResource\Pages;

use App\Filament\Resources\AssetPurchaseResource;
use App\Filament\Resources\AssetPurchaseResource\Widgets\AssetPurchaseStatsWidget;
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

class ListAssetPurchases extends ListRecords
{
    protected static string $resource = AssetPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Fieldset::make('Filter Export Pembelian Aset')
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

                                    Select::make('status_id')
                                        ->label('Status Aset')
                                        ->prefixIcon('heroicon-o-information-circle')
                                        ->searchable()
                                        ->options(MasterAssetsStatus::pluck('name', 'id'))
                                        ->placeholder('Semua Status')
                                        ->nullable(),

                                    Select::make('location_id')
                                        ->label('Lokasi')
                                        ->prefixIcon('heroicon-o-map-pin')
                                        ->searchable()
                                        ->options(MasterAssetsLocation::pluck('name', 'id'))
                                        ->placeholder('Semua Lokasi')
                                        ->live()
                                        ->nullable(),

                                    Select::make('sub_location_id')
                                        ->label('Sub Lokasi')
                                        ->prefixIcon('heroicon-o-map')
                                        ->searchable()
                                        ->options(
                                            fn($get) => $get('location_id')
                                                ? MasterAssetsSubLocation::where('location_id', $get('location_id'))->pluck('name', 'id')
                                                : []
                                        )
                                        ->placeholder('Pilih lokasi dulu')
                                        ->disabled(fn($get) => !$get('location_id'))
                                        ->nullable(),
                                ]),
                        ])
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'status_id' => $data['status_id'] ?? null,
                        'location_id' => $data['location_id'] ?? null,
                        'sub_location_id' => $data['sub_location_id'] ?? null,
                    ]));

                    return redirect()->to(route('export.asset-purchase') . '?' . $params);
                }),

            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssetPurchaseStatsWidget::class,
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

            SelectFilter::make('status_id')
                ->relationship('status', 'name')
                ->label('Status Aset')
                ->placeholder('Semua Status'),

            SelectFilter::make('location_id')
                ->relationship('location', 'name')
                ->label('Lokasi')
                ->placeholder('Semua Lokasi'),

            SelectFilter::make('sub_location_id')
                ->relationship('subLocation', 'name')
                ->label('Sub Lokasi')
                ->placeholder('Semua Sub Lokasi'),
        ];
    }
}
