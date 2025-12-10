<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterSubDepartmentsResource\Pages;
use App\Filament\Resources\MasterSubDepartmentsResource\RelationManagers;
use App\Models\MasterSubDepartments;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterSubDepartmentsResource extends Resource
{
    protected static ?string $model = MasterSubDepartments::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master';
    protected static ?string $navigationLabel = 'Sub Bagian';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Form Kecamatan')
                    ->description('Input Kecamatan pada form di bawah ini.')
                    ->schema([
                        Forms\Components\Select::make('departments_id')
                            ->relationship('Departments', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(36),
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
                Tables\Columns\TextColumn::make('Departments.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
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
            'index' => Pages\ListMasterSubDepartments::route('/'),
            //'create' => Pages\CreateMasterSubDepartments::route('/create'),
            //'edit' => Pages\EditMasterSubDepartments::route('/{record}/edit'),
        ];
    }
}
