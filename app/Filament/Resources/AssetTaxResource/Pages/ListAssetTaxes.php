<?php

namespace App\Filament\Resources\AssetTaxResource\Pages;

use App\Filament\Resources\AssetTaxResource;
use App\Models\MasterTaxType;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;

class ListAssetTaxes extends ListRecords
{
    protected static string $resource = AssetTaxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export Laporan Pajak')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Fieldset::make('Filter Export Laporan Pajak')
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2, 'lg' => 3])
                                ->schema([
                                    Select::make('tax_type_id')
                                        ->label('Jenis Pajak')
                                        ->prefixIcon('heroicon-o-document-text')
                                        ->searchable()
                                        ->options(MasterTaxType::where('is_active', true)->pluck('name', 'id'))
                                        ->placeholder('Semua Jenis Pajak')
                                        ->nullable(),

                                    Select::make('tax_year')
                                        ->label('Tahun Pajak')
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->searchable()
                                        ->options(function () {
                                            $years = [];
                                            for ($i = now()->year; $i >= now()->year - 10; $i--) {
                                                $years[$i] = $i;
                                            }
                                            return $years;
                                        })
                                        ->placeholder('Semua Tahun')
                                        ->nullable(),

                                    Select::make('payment_status')
                                        ->label('Status Pembayaran')
                                        ->prefixIcon('heroicon-o-currency-dollar')
                                        ->options([
                                            'pending' => 'Belum Dibayar',
                                            'paid' => 'Sudah Dibayar',
                                            'overdue' => 'Terlambat',
                                            'cancelled' => 'Dibatalkan',
                                        ])
                                        ->placeholder('Semua Status')
                                        ->nullable(),

                                    DatePicker::make('due_date_start')
                                        ->label('Jatuh Tempo Mulai')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->placeholder('Pilih tanggal')
                                        ->nullable(),

                                    DatePicker::make('due_date_end')
                                        ->label('Jatuh Tempo Akhir')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->placeholder('Pilih tanggal')
                                        ->nullable(),

                                    DatePicker::make('payment_date_start')
                                        ->label('Tanggal Bayar Mulai')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->placeholder('Pilih tanggal')
                                        ->nullable(),

                                    DatePicker::make('payment_date_end')
                                        ->label('Tanggal Bayar Akhir')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->placeholder('Pilih tanggal')
                                        ->nullable(),
                                ]),
                        ])
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $params = http_build_query(array_filter([
                        'tax_type_id' => $data['tax_type_id'] ?? null,
                        'tax_year' => $data['tax_year'] ?? null,
                        'payment_status' => $data['payment_status'] ?? null,
                        'due_date_start' => $data['due_date_start'] ?? null,
                        'due_date_end' => $data['due_date_end'] ?? null,
                        'payment_date_start' => $data['payment_date_start'] ?? null,
                        'payment_date_end' => $data['payment_date_end'] ?? null,
                    ]));

                    return redirect()->to(route('export.asset-tax') . '?' . $params);
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn() => $this->getModel()::count()),

            'pending_approval' => Tab::make('Menunggu Approval')
                ->modifyQueryUsing(fn(Builder $query) => $query->pendingApproval())
                ->badge(fn() => $this->getModel()::pendingApproval()->count())
                ->badgeColor('warning'),

            'unpaid' => Tab::make('Belum Dibayar')
                ->modifyQueryUsing(fn(Builder $query) => $query->unpaid())
                ->badge(fn() => $this->getModel()::unpaid()->count())
                ->badgeColor('danger'),

            'overdue' => Tab::make('Terlambat')
                ->modifyQueryUsing(fn(Builder $query) => $query->overdue())
                ->badge(fn() => $this->getModel()::overdue()->count())
                ->badgeColor('danger'),

            'upcoming' => Tab::make('Akan Jatuh Tempo')
                ->modifyQueryUsing(fn(Builder $query) => $query->upcoming(30))
                ->badge(fn() => $this->getModel()::upcoming(30)->count())
                ->badgeColor('warning'),

            'paid' => Tab::make('Sudah Dibayar')
                ->modifyQueryUsing(fn(Builder $query) => $query->paid())
                ->badge(fn() => $this->getModel()::paid()->count())
                ->badgeColor('success'),
        ];
    }
}
