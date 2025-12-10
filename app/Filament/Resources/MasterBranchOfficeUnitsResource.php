<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterBranchOfficeUnitsResource\Pages;
use App\Filament\Resources\MasterBranchOfficeUnitsResource\RelationManagers;
use App\Models\MasterBranchOfficeUnits;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterBranchOfficeUnitsResource extends Resource
{
    protected static ?string $model = MasterBranchOfficeUnits::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master';
    protected static ?string $navigationLabel = 'Cabang Unit';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_unit_id')
                    ->relationship('BranchUnitOffice', 'name')
                    ->label('Nama Unit')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('branch_office_id')
                    ->relationship('BranchOfficeUnit', 'name')
                    ->label('Nama Cabang')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Hidden::make('users_id')
                    ->default(auth()->id()),
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
                Tables\Columns\TextColumn::make('branch_unit_id')
                    ->label('Nama Cabang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('branch_office_id')
                    ->label('Nama Unit')
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
            'index' => Pages\ListMasterBranchOfficeUnits::route('/'),
            //'create' => Pages\CreateMasterBranchOfficeUnits::route('/create'),
            //'edit' => Pages\EditMasterBranchOfficeUnits::route('/{record}/edit'),
        ];
    }
}
