<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterAssetsConditionResource\Pages;
use App\Filament\Resources\MasterAssetsConditionResource\RelationManagers;
use App\Models\MasterAssetsCondition;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterAssetsConditionResource extends Resource
{
    protected static ?string $model = MasterAssetsCondition::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Aset';
    protected static ?string $navigationLabel = 'Kondisi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Form Kondisi Aset')
                    ->description('Input kondisi aset yang dibutuhkan')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('desc')
                            ->label('Keterangan'),
                        Forms\Components\Hidden::make('users_id')
                            ->default(auth()->id()),
                    ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('desc')
                    ->label('Keterangan')
                    ->sortable()
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
            'index' => Pages\ListMasterAssetsConditions::route('/'),
            //'create' => Pages\CreateMasterAssetsCondition::route('/create'),
            //'edit' => Pages\EditMasterAssetsCondition::route('/{record}/edit'),
        ];
    }
}
