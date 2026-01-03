<?php

namespace App\Filament\Widgets;

use App\Models\AssetDisposal;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AssetDisposalTable extends BaseWidget
{
    protected static ?int $sort = 17;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Penghapusan Barang')
            ->query(
                AssetDisposal::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('assetDisposals.assets_number')
                    ->label('Nomor Aset')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('assetDisposals.name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('disposal_type')
                    ->label('Jenis Penghapusan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sale' => 'success',
                        'donation' => 'info',
                        'destruction' => 'danger',
                        'transfer' => 'warning',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('disposal_value')
                    ->label('Nilai Penghapusan')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('disposal_date')
                    ->label('Tanggal Penghapusan')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('disposal_date', 'desc');
    }
}
