<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use App\Models\MasterDepartments;
use App\Models\MasterSubDepartments;
use App\Models\MasterEmployeePosition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Master Pegawai';

    protected static ?string $navigationLabel = 'Pegawai';

    protected static ?string $modelLabel = 'Pegawai';

    protected static ?string $pluralModelLabel = 'Pegawai';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Pegawai')
                    ->schema([
                        Forms\Components\TextInput::make('nippam')
                            ->label('NIP/NIPPAM')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('departments_id')
                            ->label('Bagian')
                            ->options(MasterDepartments::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('sub_department_id', null)),

                        Forms\Components\Select::make('sub_department_id')
                            ->label('Sub Bagian')
                            ->options(function (Forms\Get $get) {
                                $departmentId = $get('departments_id');
                                if (!$departmentId) {
                                    return [];
                                }
                                return MasterSubDepartments::where('departments_id', $departmentId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('employee_position_id')
                            ->label('Jabatan')
                            ->options(MasterEmployeePosition::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Forms\Components\Hidden::make('users_id')
                            ->default(fn() => auth()->id()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nippam')
                    ->label('NIP/NIPPAM')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Bagian')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subDepartment.name')
                    ->label('Sub Bagian')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position.name')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('departments_id')
                    ->label('Bagian')
                    ->options(MasterDepartments::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('sub_department_id')
                    ->label('Sub Bagian')
                    ->options(MasterSubDepartments::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
