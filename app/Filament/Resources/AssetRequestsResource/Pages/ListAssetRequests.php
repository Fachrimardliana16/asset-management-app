<?php

namespace App\Filament\Resources\AssetRequestsResource\Pages;

use App\Filament\Resources\AssetRequestsResource;
use App\Filament\Resources\AssetRequestsResource\Widgets\AssetRequestsStatsWidget;
use App\Models\Employee; // Sesuaikan dengan model karyawanmu
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

class ListAssetRequests extends ListRecords
{
    protected static string $resource = AssetRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Fieldset::make('Filter Export Permintaan Aset')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('start_date')
                                        ->label('Tanggal Permintaan Mulai')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->placeholder('Pilih tanggal')
                                        ->default(now()->startOfMonth()),

                                    DatePicker::make('end_date')
                                        ->label('Tanggal Permintaan Akhir')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->placeholder('Pilih tanggal')
                                        ->default(now()->endOfMonth()),

                                    Select::make('purchase_status')
                                        ->label('Status Pembelian')
                                        ->prefixIcon('heroicon-o-information-circle')
                                        ->searchable()
                                        ->options([
                                            'pending' => 'Pending',
                                            'in_progress' => 'Sedang Diproses',
                                            'purchased' => 'Sudah Dibeli',
                                            'rejected' => 'Ditolak',
                                        ])
                                        ->placeholder('Semua Status')
                                        ->nullable(),

                                    Select::make('employee_id')
                                        ->label('Pemohon')
                                        ->prefixIcon('heroicon-o-user')
                                        ->searchable()
                                        ->options(Employee::pluck('name', 'id')) // Sesuaikan dengan field nama karyawan
                                        ->placeholder('Semua Karyawan')
                                        ->nullable(),
                                ]),
                        ])
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'purchase_status' => $data['purchase_status'] ?? null,
                        'employee_id' => $data['employee_id'] ?? null,
                    ]));

                    return redirect()->to(route('export.asset-requests') . '?' . $params);
                }),

            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssetRequestsStatsWidget::class,
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Filter::make('request_date_range')
                ->label('Rentang Tanggal Permintaan')
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
                        ->when($data['start_date'], fn($q) => $q->whereDate('date', '>=', $data['start_date']))
                        ->when($data['end_date'], fn($q) => $q->whereDate('date', '<=', $data['end_date']));
                })
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['start_date'] && !$data['end_date']) return null;

                    $start = $data['start_date'] ? Carbon::parse($data['start_date'])->format('d/m/Y') : '...';
                    $end = $data['end_date'] ? Carbon::parse($data['end_date'])->format('d/m/Y') : '...';

                    return "Permintaan: {$start} → {$end}";
                }),

            SelectFilter::make('purchase_status')
                ->label('Status Pembelian')
                ->options([
                    'pending' => 'Pending',
                    'in_progress' => 'Sedang Diproses',
                    'purchased' => 'Sudah Dibeli',
                    'rejected' => 'Ditolak',
                ])
                ->placeholder('Semua Status'),

            SelectFilter::make('employee_id')
                ->relationship('employee', 'name') // Sesuaikan relasi & field nama di model Employee
                ->label('Pemohon')
                ->searchable()
                ->placeholder('Semua Karyawan'),
        ];
    }
}
