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

    protected static bool $isLazy = true;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Aset Rusak')
            ->query(
                Asset::query()
                    ->with(['categoryAsset', 'conditionAsset'])
                    ->whereIn('condition_id', [2, 3]) // 2 = Rusak, 3 = Perbaikan
                    ->latest()
                    ->limit(10)
            )
            ->poll(null)
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
                    ->default('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('conditionAsset.name')
                    ->label('Kondisi')
                    ->badge()
                    ->default('-')
                    ->color(fn(?string $state): string => match ($state) {
                        'Rusak' => 'danger',
                        'Perbaikan' => 'warning',
                        null => 'gray',
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
