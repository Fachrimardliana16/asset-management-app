<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetMonitoringResource\Pages;
use App\Filament\Resources\AssetMonitoringResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetMonitoring;
use App\Models\AssetMutation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssetMonitoringResource extends Resource
{
    protected static ?string $model = AssetMonitoring::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Asset';
    protected static ?string $navigationLabel = 'Riwayat Monitoring';
    protected static ?int $navigationSort = 5;
    protected static ?string $modelLabel = 'Riwayat Monitoring';
    protected static ?string $pluralModelLabel = 'Riwayat Monitoring';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Form Monitoring Barang')
                    ->description('Input data monitoring barang')
                    ->schema([
                        Forms\Components\DatePicker::make('monitoring_date')
                            ->label('Tanggal Monitoring')
                            ->required(),
                        Forms\Components\Select::make('assets_id')
                            ->options(
                                Asset::query()
                                    ->get()
                                    ->mapWithKeys(function ($asset) {
                                        // Menggabungkan 'assets_number' dan 'name' dengan format yang diinginkan
                                        return [$asset->id => $asset->assets_number . ' | ' . $asset->name];
                                    })
                                    ->toArray()
                            )
                            ->afterStateUpdated(function ($set, $state) {
                                $aset = Asset::find($state);
                                if ($aset) {
                                    $set('assets_number', $aset->assets_number);
                                    $set('name', $aset->name);
                                    $set('old_condition_id', $aset->condition_id);
                                } else {
                                    $set('assets_number', null);
                                    $set('name', null);
                                    $set('old_condition_id', null);
                                }
                            })
                            ->label('Nomor Aset')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        Forms\Components\Hidden::make('assets_number')
                            ->default(function ($get) {
                                $assets_id = $get('assets_id');
                                $asset = AssetMutation::find($assets_id);
                                return $asset ? $asset->assets_number : null;
                            }),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Aset')
                            ->required()
                            ->readOnly(),
                        Forms\Components\Select::make('old_condition_id')
                            ->relationship('MonitoringoldCondition', 'name')
                            ->label('Kondisi Lama')
                            ->required()
                            ->disabled(),
                        Forms\Components\Hidden::make('old_condition_id')
                            ->default(function ($get) {
                                return $get('old_condition_id');
                            }),
                        Forms\Components\Select::make('new_condition_id')
                            ->relationship('MonitoringNewCondition', 'name')
                            ->label('Kondisi Baru')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Textarea::make('desc')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('users_id')
                            ->default(auth()->id()),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('monitoring_date')
                    ->label('Tanggal Monitoring')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assetMonitoring.assets_number')
                    ->label('Nomor Aset')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('assetMonitoring.name')
                    ->label('Nama Aset')
                    ->searchable(),
                Tables\Columns\TextColumn::make('MonitoringoldCondition.name')
                    ->label('Kondisi Lama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('MonitoringNewCondition.name')
                    ->label('Kondisi Baru')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListAssetMonitorings::route('/'),
            'create' => Pages\CreateAssetMonitoring::route('/create'),
            'edit' => Pages\EditAssetMonitoring::route('/{record}/edit'),
        ];
    }

    /**
     * Add eager loading to prevent N+1 query issues
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'assetMonitoring',
                'MonitoringoldCondition',
                'MonitoringNewCondition',
            ]);
    }
}
