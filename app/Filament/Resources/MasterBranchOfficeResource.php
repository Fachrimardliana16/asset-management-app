<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterBranchOfficeResource\Pages;
use App\Filament\Resources\MasterBranchOfficeResource\RelationManagers;
use App\Models\MasterBranchOffice;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterBranchOfficeResource extends Resource
{
    protected static ?string $model = MasterBranchOffice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master';
    protected static ?string $navigationLabel = 'Cabang';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Form Input Kantor Cabang')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('branch_unit_id')
                            ->relationship('BranchOffice', 'name')
                            ->label('Kantor Unit')
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kantor Cabang')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telp')
                            ->tel()
                            ->maxLength(255),
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
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('BranchOffice.name')
                    ->label('Kantor Unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
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
            'index' => Pages\ListMasterBranchOffices::route('/'),
            //'create' => Pages\CreateMasterBranchOffice::route('/create'),
            //'edit' => Pages\EditMasterBranchOffice::route('/{record}/edit'),
        ];
    }
}
