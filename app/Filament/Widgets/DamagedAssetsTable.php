<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DamagedAssetsTable extends BaseWidget
{
    protected static ?int $sort = 12;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Aset Rusak')
            ->query(
                Asset::query()
                    ->whereIn('condition_id', [2, 3]) // 2 = Rusak, 3 = Perbaikan
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('assets_number')
                    ->label('Nomor Aset')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('categoryAsset.name')
                    ->label('Kategori')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('conditionAsset.name')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Rusak' => 'danger',
                        'Perbaikan' => 'warning',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Tanggal Beli')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
