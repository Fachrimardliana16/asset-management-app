<?php

namespace App\Filament\Resources\AssetMonitoringResource\Pages;

use App\Filament\Resources\AssetMonitoringResource;
use App\Filament\Resources\AssetMonitoringResource\Widgets\AssetMonitoringStatsWidget;
use App\Models\MasterAssetsCondition;
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
                    Fieldset::make('Filter Export Monitoring Aset')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('start_date')
                                        ->label('Tanggal Mulai')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->placeholder('Pilih tanggal mulai')
                                        ->default(now()->startOfMonth()),

                                    DatePicker::make('end_date')
                                        ->label('Tanggal Akhir')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->placeholder('Pilih tanggal akhir')
                                        ->default(now()->endOfMonth()),

                                    Select::make('new_condition')
                                        ->label('Kondisi Baru')
                                        ->prefixIcon('heroicon-o-exclamation-triangle')
                                        ->searchable()
                                        ->options(MasterAssetsCondition::pluck('name', 'id'))
                                        ->placeholder('Semua Kondisi')
                                        ->nullable(),
                                ]),
                        ])
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
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

    protected function getTableFilters(): array
    {
        return [
            Filter::make('date_range')
                ->label('Rentang Tanggal Monitoring')
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
                        ->when(
                            $data['start_date'],
                            fn(Builder $q) => $q->whereDate('monitoring_date', '>=', $data['start_date'])
                        )
                        ->when(
                            $data['end_date'],
                            fn(Builder $q) => $q->whereDate('monitoring_date', '<=', $data['end_date'])
                        );
                })
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['start_date'] && !$data['end_date']) {
                        return null;
                    }

                    $start = $data['start_date']
                        ? Carbon::parse($data['start_date'])->format('d/m/Y')
                        : '...';

                    $end = $data['end_date']
                        ? Carbon::parse($data['end_date'])->format('d/m/Y')
                        : '...';

                    return "Tanggal: {$start} → {$end}";
                }),

            SelectFilter::make('new_condition')
                ->label('Kondisi Baru')
                ->options(MasterAssetsCondition::pluck('name', 'id'))
                ->placeholder('Semua Kondisi'),
        ];
    }
}
