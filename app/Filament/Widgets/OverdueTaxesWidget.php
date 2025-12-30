<?php

namespace App\Filament\Widgets;

use App\Models\AssetTax;
use App\Filament\Resources\AssetTaxResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OverdueTaxesWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Pajak yang Terlambat';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AssetTax::query()
                    ->with(['asset', 'taxType'])
                    ->overdue()
                    ->orderBy('due_date', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('asset.name')
                    ->label('Aset')
                    ->searchable()
                    ->limit(30),
                
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
                
                Tables\Columns\TextColumn::make('penalty_amount')
                    ->label('Denda')
                    ->money('IDR')
                    ->color('danger')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->color('danger')
                    ->description(function ($record) {
                        if (!$record) return '';
                        $daysOverdue = $record->getOverdueDaysCount();
                        return 'Terlambat ' . $daysOverdue . ' hari';
                    }),
                
                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Approval')
                    ->colors([
                        'success' => 'approved',
                        'warning' => 'pending',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'approved' => 'Disetujui',
                        'pending' => 'Pending',
                        'rejected' => 'Ditolak',
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
