<?php

namespace App\Filament\Widgets;

use App\Models\AssetTax;
use App\Filament\Resources\AssetTaxResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingTaxesWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Pajak yang Akan Jatuh Tempo';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AssetTax::query()
                    ->with(['asset', 'taxType'])
                    ->upcoming(30)
                    ->orderBy('due_date', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('asset.name')
                    ->label('Aset')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('asset.asset_code')
                    ->label('Kode Aset')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('taxType.name')
                    ->label('Jenis Pajak')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('tax_year')
                    ->label('Tahun')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('tax_amount')
                    ->label('Nilai Pajak')
                    ->money('IDR'),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->badge()
                    ->color(function ($record) {
                        if (!$record) return 'success';
                        $daysLeft = now()->diffInDays($record->due_date, false);
                        if ($daysLeft <= 7) return 'danger';
                        if ($daysLeft <= 14) return 'warning';
                        return 'success';
                    })
                    ->description(function ($record) {
                        if (!$record) return '';
                        $daysLeft = now()->diffInDays($record->due_date, false);
                        if ($daysLeft == 0) return 'Hari ini!';
                        if ($daysLeft == 1) return 'Besok';
                        return $daysLeft . ' hari lagi';
                    }),
                
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'danger' => 'overdue',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Pending',
                        'overdue' => 'Terlambat',
                        default => $state,
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => $record ? AssetTaxResource::getUrl('view', ['record' => $record]) : null),
            ]);
    }
}
