<?php

namespace App\Filament\Widgets;

use App\Models\AssetTax;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class UpcomingTaxesTable extends BaseWidget
{
    protected static ?int $sort = 16;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pajak yang Akan Dibayar Seminggu')
            ->query(
                AssetTax::query()
                    ->whereIn('payment_status', ['unpaid', 'pending'])
                    ->whereBetween('due_date', [
                        Carbon::now(),
                        Carbon::now()->addWeek()
                    ])
                    ->orderBy('due_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('asset.assets_number')
                    ->label('Nomor Aset')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('asset.name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('taxType.name')
                    ->label('Jenis Pajak')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tax_amount')
                    ->label('Jumlah Pajak')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn($record) => Carbon::parse($record->due_date)->isPast() ? 'danger' : 'warning'),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('due_date', 'asc');
    }
}
