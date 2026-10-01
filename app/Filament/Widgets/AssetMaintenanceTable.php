<?php

namespace App\Filament\Widgets;

use App\Models\AssetMaintenance;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AssetMaintenanceTable extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 15;
    protected int | string | array $columnSpan = 1;
    protected static bool $isLazy = true;
    public function table(Table $table): Table
    {
        return $table
            ->heading('Pemeliharaan Barang')
            ->query(
                AssetMaintenance::query()
                    ->with(['AssetMaintenance'])
                    ->latest()
                    ->limit(10)
            )
            ->poll(null)
            ->columns([
                Tables\Columns\TextColumn::make('AssetMaintenance.assets_number')
                    ->label('Nomor Aset')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('AssetMaintenance.name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Jenis Pemeliharaan')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'preventive' => 'success',
                        'corrective' => 'warning',
                        'predictive' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('service_cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('maintenance_date')
                    ->label('Tanggal Pemeliharaan')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('maintenance_date', 'desc');
    }
}
