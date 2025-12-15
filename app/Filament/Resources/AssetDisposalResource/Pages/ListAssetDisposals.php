<?php

namespace App\Filament\Resources\AssetDisposalResource\Pages;

use App\Filament\Resources\AssetDisposalResource;
use App\Filament\Resources\AssetDisposalResource\Widgets\AssetDisposalStatsWidget;
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

class ListAssetDisposals extends ListRecords
{
    protected static string $resource = AssetDisposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Fieldset::make('Filter Export Penghapusan Aset')
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
                                ]),
                            Select::make('disposal_process')
                                ->label('Proses Penghapusan')
                                ->prefixIcon('heroicon-o-funnel')
                                ->searchable()
                                ->options([
                                    'dimusnahkan' => 'Dimusnahkan',
                                    'dijual' => 'Dijual',
                                    'dihibahkan' => 'Dihibahkan',
                                    'dihapus dari inventaris' => 'Dihapus dari Daftar Inventaris',
                                ])
                                ->placeholder('Semua Proses')
                                ->nullable()
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'disposal_process' => $data['disposal_process'] ?? null,
                    ]));

                    return redirect()->to(route('export.asset-disposal') . '?' . $params);
                }),

            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssetDisposalStatsWidget::class,
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Filter::make('date_range')
                ->label('Rentang Tanggal Penghapusan')
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
                            fn(Builder $q) => $q->whereDate('disposal_date', '>=', $data['start_date'])
                        )
                        ->when(
                            $data['end_date'],
                            fn(Builder $q) => $q->whereDate('disposal_date', '<=', $data['end_date'])
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

            SelectFilter::make('disposal_process')
                ->label('Proses Penghapusan')
                ->options([
                    'dimusnahkan' => 'Dimusnahkan',
                    'dijual' => 'Dijual',
                    'dihibahkan' => 'Dihibahkan',
                    'dihapus dari inventaris' => 'Dihapus dari Daftar Inventaris',
                ])
                ->placeholder('Semua Proses'),
        ];
    }
}
