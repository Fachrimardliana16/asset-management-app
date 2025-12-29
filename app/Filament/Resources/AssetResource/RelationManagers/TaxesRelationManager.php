<?php

namespace App\Filament\Resources\AssetResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Models\MasterTaxType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TaxesRelationManager extends RelationManager
{
    protected static string $relationship = 'taxes';
    
    protected static ?string $title = 'Histori Pajak';
    
    protected static ?string $recordTitleAttribute = 'tax_year';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tax_type_id')
                    ->label('Jenis Pajak')
                    ->options(function () {
                        $asset = $this->getOwnerRecord();
                        return MasterTaxType::where('asset_category_id', $asset->category_id)
                            ->where('is_active', true)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable(),
                
                Forms\Components\TextInput::make('tax_year')
                    ->label('Tahun Pajak')
                    ->numeric()
                    ->default(now()->year)
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->required(),
                
                Forms\Components\TextInput::make('tax_amount')
                    ->label('Nilai Pajak')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->minValue(0),
                
                Forms\Components\DatePicker::make('due_date')
                    ->label('Tanggal Jatuh Tempo')
                    ->required()
                    ->native(false),
                
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Tanggal Pembayaran')
                    ->native(false)
                    ->maxDate(now()),
                
                Forms\Components\Select::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Lunas',
                        'overdue' => 'Terlambat',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('pending')
                    ->required(),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tax_year')
            ->columns([
                Tables\Columns\TextColumn::make('taxType.name')
                    ->label('Jenis Pajak')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('tax_year')
                    ->label('Tahun')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('tax_amount')
                    ->label('Nilai Pajak')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('penalty_amount')
                    ->label('Denda')
                    ->money('IDR')
                    ->sortable()
                    ->color('danger')
                    ->default(0),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->getStateUsing(fn ($record) => $record->tax_amount + $record->penalty_amount),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : 'success'),
                
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tgl Bayar')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),
                
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Status')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger' => 'overdue',
                        'secondary' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'paid' => 'Lunas',
                        'pending' => 'Pending',
                        'overdue' => 'Terlambat',
                        'cancelled' => 'Batal',
                        default => $state,
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
            ->filters([
                Tables\Filters\SelectFilter::make('tax_type_id')
                    ->label('Jenis Pajak')
                    ->relationship('taxType', 'name'),
                
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Lunas',
                        'overdue' => 'Terlambat',
                        'cancelled' => 'Dibatalkan',
                    ]),
                
                Tables\Filters\SelectFilter::make('tax_year')
                    ->label('Tahun')
                    ->options(function () {
                        $years = [];
                        for ($i = now()->year; $i >= now()->year - 10; $i--) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pajak')
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['approval_status'] = 'pending';
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    
                    Tables\Actions\Action::make('pay')
                        ->label('Bayar')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->form([
                            Forms\Components\DatePicker::make('payment_date')
                                ->label('Tanggal Pembayaran')
                                ->required()
                                ->default(now())
                                ->maxDate(now())
                                ->native(false),
                            Forms\Components\Textarea::make('payment_notes')
                                ->label('Catatan Pembayaran')
                                ->rows(3),
                        ])
                        ->visible(fn ($record) => $record->payment_status !== 'paid')
                        ->action(function ($record, array $data) {
                            $record->update([
                                'payment_status' => 'paid',
                                'payment_date' => $data['payment_date'],
                                'paid_by' => Auth::id(),
                                'notes' => ($record->notes ? $record->notes . "\n\n" : '') . 
                                          "Pembayaran: " . ($data['payment_notes'] ?? ''),
                            ]);
                            
                            Notification::make()
                                ->title('Pembayaran Berhasil')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\Action::make('update_penalty')
                        ->label('Update Denda')
                        ->icon('heroicon-o-calculator')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->updatePenalty();
                            
                            Notification::make()
                                ->title('Denda Diperbarui')
                                ->body("Denda: Rp " . number_format($record->penalty_amount, 0, ',', '.'))
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tax_year', 'desc')
            ->emptyStateHeading('Belum Ada Histori Pajak')
            ->emptyStateDescription('Tambahkan data pajak untuk aset ini.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}

