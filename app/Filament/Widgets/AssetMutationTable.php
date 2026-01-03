<?php

namespace App\Filament\Widgets;

use App\Models\AssetMutation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AssetMutationTable extends BaseWidget
{
    protected static ?int $sort = 14;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Mutasi Perpindahan Barang')
            ->query(
                AssetMutation::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('mutations_number')
                    ->label('Nomor Mutasi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assets_number')
                    ->label('Nomor Aset')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('AssetsMutationemployee.name')
                    ->label('Pegawai')
                    ->sortable(),

                Tables\Columns\TextColumn::make('AssetsMutationlocation.name')
                    ->label('Lokasi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('AssetsMutationsubLocation.name')
                    ->label('Sub Lokasi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('mutation_date')
                    ->label('Tanggal Mutasi')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('mutation_date', 'desc');
    }
}
