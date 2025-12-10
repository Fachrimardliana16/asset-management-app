<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetMaintenanceResource\Pages;
use App\Filament\Resources\AssetMaintenanceResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssetMaintenanceResource extends Resource
{
    protected static ?string $model = AssetMaintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Asset';
    protected static ?string $navigationLabel = 'Pemeliharaan Barang';
    protected static ?string $modelLabel = 'Pemeliharaan Barang';
    protected static ?string $pluralModelLabel = 'Pemeliharaan Barang';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Form Pemeliharaan/Perbaikan Aset')
                    ->description('Input data pemeliharaan atau perbaikan aset')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\DatePicker::make('maintenance_date')
                                ->label('Tanggal Pemeliharaan')
                                ->required()
                                ->default(now()),
                            Forms\Components\Select::make('service_type')
                                ->options([
                                    'Perbaikan Ringan' => 'Perbaikan Ringan',
                                    'Perbaikan Sedang' => 'Perbaikan Sedang',
                                    'Perbaikan Berat' => 'Perbaikan Berat',
                                    'Perawatan Berkala' => 'Perawatan Berkala',
                                ])
                                ->label('Jenis Perbaikan')
                                ->required()
                                ->native(false),
                        ]),
                        Forms\Components\Section::make('Data Aset')
                            ->schema([
                                Forms\Components\Select::make('assets_id')
                                    ->label('Pilih Aset')
                                    ->options(
                                        Asset::query()
                                            ->whereHas('assetsStatus', fn($q) => $q->where('name', 'Active'))
                                            ->get()
                                            ->mapWithKeys(fn($asset) => [$asset->id => $asset->assets_number . ' - ' . $asset->name])
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih aset yang akan diperbaiki'),
                            ])->collapsible(),
                        Forms\Components\Section::make('Detail Perbaikan')
                            ->schema([
                                Forms\Components\TextInput::make('location_service')
                                    ->label('Lokasi Service/Tempat Perbaikan')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Bengkel ABC, Workshop Internal, dll'),
                                Forms\Components\TextInput::make('service_cost')
                                    ->label('Biaya Perbaikan')
                                    ->prefix('Rp')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Total biaya perbaikan'),
                                Forms\Components\Textarea::make('desc')
                                    ->label('Deskripsi Kerusakan & Perbaikan')
                                    ->placeholder('Jelaskan kerusakan yang terjadi dan tindakan perbaikan yang dilakukan...')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])->columns(2)->collapsible(),
                        Forms\Components\Section::make('Dokumen')
                            ->schema([
                                Forms\Components\FileUpload::make('invoice_file')
                                    ->label('Bukti/Struk/Invoice')
                                    ->directory('Maintenance')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->helperText('Upload struk atau invoice perbaikan'),
                            ])->collapsible(),
                        Forms\Components\Hidden::make('users_id')
                            ->default(auth()->id()),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('No.')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('maintenance_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('AssetMaintenance.assets_number')
                    ->label('No. Aset')
                    ->searchable(),
                Tables\Columns\TextColumn::make('AssetMaintenance.name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Jenis Perbaikan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Perbaikan Ringan' => 'info',
                        'Perbaikan Sedang' => 'warning',
                        'Perbaikan Berat' => 'danger',
                        'Perawatan Berkala' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('location_service')
                    ->label('Lokasi Service')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service_cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc')
                    ->label('Keterangan')
                    ->wrap()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('maintenance_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('service_type')
                    ->options([
                        'Perbaikan Ringan' => 'Perbaikan Ringan',
                        'Perbaikan Sedang' => 'Perbaikan Sedang',
                        'Perbaikan Berat' => 'Perbaikan Berat',
                        'Perawatan Berkala' => 'Perawatan Berkala',
                    ])
                    ->label('Jenis Perbaikan'),
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
            ]);
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
            'index' => Pages\ListAssetMaintenances::route('/'),
            'create' => Pages\CreateAssetMaintenance::route('/create'),
            'edit' => Pages\EditAssetMaintenance::route('/{record}/edit'),
        ];
    }
}
