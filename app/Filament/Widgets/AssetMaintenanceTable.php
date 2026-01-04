<?php

namespace App\Filament\Widgets;

use App\Models\AssetMaintenance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AssetMaintenanceTable extends BaseWidget
{
    protected static ?int $sort = 15;
    protected int | string | array $columnSpan = 1;
    protected static bool $isLazy = true;
    public function table(Table $table): Table
    {
        return $table
            ->heading('Pemeliharaan Barang')
            ->query(
                AssetMaintenance::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('assetMaintenance.assets_number')
                    ->label('Nomor Aset')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assetMaintenance.name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('maintenance_type')
                    ->label('Jenis Pemeliharaan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'preventive' => 'success',
                        'corrective' => 'warning',
                        'predictive' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('maintenance_date')
                    ->label('Tanggal Pemeliharaan')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'warning',
                        'scheduled' => 'info',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('maintenance_date', 'desc');
    }
}
