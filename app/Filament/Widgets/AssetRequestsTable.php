<?php

namespace App\Filament\Widgets;

use App\Models\AssetRequests;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AssetRequestsTable extends BaseWidget
{
    protected static ?int $sort = 10;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Permintaan Barang')
            ->query(
                AssetRequests::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('document_number')
                    ->label('Nomor Dokumen')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Pemohon')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_items')
                    ->label('Jumlah Jenis')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Total Unit')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_request')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Disetujui' : 'Pending')
                    ->color(fn($state): string => $state ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal Permintaan')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('date', 'desc');
    }
}
