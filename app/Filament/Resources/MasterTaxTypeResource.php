<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterTaxTypeResource\Pages;
use App\Models\MasterTaxType;
use App\Models\MasterAssetsCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterTaxTypeResource extends Resource
{
    protected static ?string $model = MasterTaxType::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Jenis Pajak';

    protected static ?string $modelLabel = 'Jenis Pajak';

    protected static ?string $pluralModelLabel = 'Master Jenis Pajak';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 15;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Jenis Pajak')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: PKB, BPKB, PBB, IMB'),
                        
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Pajak')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Contoh: PKB, PBB'),
                        
                        Forms\Components\Select::make('asset_category_id')
                            ->label('Kategori Aset')
                            ->relationship('assetCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih kategori aset yang terkait')
                            ->helperText('Kategori aset yang memerlukan jenis pajak ini'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Periode Pajak')
                    ->schema([
                        Forms\Components\Select::make('period_type')
                            ->label('Tipe Periode')
                            ->options([
                                'yearly' => 'Tahunan (12 Bulan)',
                                '5yearly' => '5 Tahunan (60 Bulan)',
                                'custom' => 'Custom',
                            ])
                            ->default('yearly')
                            ->required()
                            ->live()
                            ->helperText('Frekuensi pembayaran pajak'),
                        
                        Forms\Components\TextInput::make('period_months')
                            ->label('Periode (Bulan)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(120)
                            ->visible(fn (Forms\Get $get) => $get('period_type') === 'custom')
                            ->required(fn (Forms\Get $get) => $get('period_type') === 'custom')
                            ->helperText('Jumlah bulan untuk periode custom'),
                        
                        Forms\Components\TextInput::make('reminder_days')
                            ->label('Reminder (Hari)')
                            ->numeric()
                            ->default(30)
                            ->required()
                            ->minValue(1)
                            ->maxValue(365)
                            ->helperText('Berapa hari sebelum jatuh tempo notifikasi dikirim'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengaturan Denda')
                    ->schema([
                        Forms\Components\Toggle::make('has_penalty')
                            ->label('Memiliki Denda')
                            ->default(false)
                            ->live()
                            ->helperText('Aktifkan jika pajak ini memiliki denda keterlambatan'),
                        
                        Forms\Components\Select::make('penalty_type')
                            ->label('Tipe Denda')
                            ->options([
                                'percentage' => 'Persentase',
                                'fixed' => 'Nominal Tetap',
                            ])
                            ->default('percentage')
                            ->required(fn (Forms\Get $get) => $get('has_penalty'))
                            ->visible(fn (Forms\Get $get) => $get('has_penalty'))
                            ->live(),
                        
                        Forms\Components\TextInput::make('penalty_percentage')
                            ->label(fn (Forms\Get $get) => 
                                $get('penalty_type') === 'fixed' ? 'Nominal Denda (Rp)' : 'Persentase Denda (%)'
                            )
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required(fn (Forms\Get $get) => $get('has_penalty'))
                            ->visible(fn (Forms\Get $get) => $get('has_penalty'))
                            ->prefix(fn (Forms\Get $get) => 
                                $get('penalty_type') === 'fixed' ? 'Rp' : null
                            )
                            ->suffix(fn (Forms\Get $get) => 
                                $get('penalty_type') === 'percentage' ? '%' : null
                            )
                            ->helperText(fn (Forms\Get $get) => 
                                $get('penalty_type') === 'fixed' 
                                    ? 'Nominal denda tetap' 
                                    : 'Persentase dari nilai pajak'
                            ),
                        
                        Forms\Components\Select::make('penalty_period')
                            ->label('Periode Perhitungan')
                            ->options([
                                'daily' => 'Per Hari',
                                'monthly' => 'Per Bulan',
                            ])
                            ->default('monthly')
                            ->required(fn (Forms\Get $get) => $get('has_penalty') && $get('penalty_type') === 'percentage')
                            ->visible(fn (Forms\Get $get) => $get('has_penalty') && $get('penalty_type') === 'percentage')
                            ->helperText('Cara perhitungan denda'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Nonaktifkan jika jenis pajak ini tidak digunakan lagi'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Jenis Pajak')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('assetCategory.name')
                    ->label('Kategori Aset')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->default('-'),
                
                Tables\Columns\TextColumn::make('period_label')
                    ->label('Periode')
                    ->badge()
                    ->color('warning'),
                
                Tables\Columns\IconColumn::make('has_penalty')
                    ->label('Denda')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('penalty_percentage')
                    ->label('Nilai Denda')
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->has_penalty 
                            ? ($record->penalty_type === 'fixed' 
                                ? 'Rp ' . number_format($state, 0, ',', '.') 
                                : $state . '%')
                            : '-'
                    )
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('reminder_days')
                    ->label('Reminder')
                    ->suffix(' hari')
                    ->alignCenter()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('asset_category_id')
                    ->label('Kategori Aset')
                    ->relationship('assetCategory', 'name')
                    ->multiple()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('period_type')
                    ->label('Tipe Periode')
                    ->options([
                        'yearly' => 'Tahunan',
                        '5yearly' => '5 Tahunan',
                        'custom' => 'Custom',
                    ]),
                
                Tables\Filters\TernaryFilter::make('has_penalty')
                    ->label('Memiliki Denda')
                    ->placeholder('Semua')
                    ->trueLabel('Dengan Denda')
                    ->falseLabel('Tanpa Denda'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterTaxTypes::route('/'),
            'create' => Pages\CreateMasterTaxType::route('/create'),
            'edit' => Pages\EditMasterTaxType::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }
}
