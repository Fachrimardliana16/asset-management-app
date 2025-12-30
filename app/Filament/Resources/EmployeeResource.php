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
                    ->description('Isi data pegawai dengan lengkap dan benar.')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nippam')
                                    ->label('NIPPAM')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20)
                                    ->placeholder('Contoh: 12345678')
                                    ->helperText('Nomor Induk Pegawai PAM (unik, tidak boleh sama dengan yang lain)')
                                    ->rules(['regex:/^\d{8,20}$/'])
                                    ->validationMessages([
                                        'regex' => 'NIPPAM harus berupa angka minimal 8 digit.',
                                        'unique' => 'NIPPAM ini sudah digunakan oleh pegawai lain.',
                                    ]),

                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Budi Santoso')
                                    ->helperText('Masukkan nama lengkap tanpa gelar'),

                                Forms\Components\Select::make('departments_id')
                                    ->label('Bagian')
                                    ->options(MasterDepartments::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Pilih bagian terlebih dahulu')
                                    ->required()
                                    ->afterStateUpdated(fn(Forms\Set $set) => $set('sub_department_id', null))
                                    ->helperText('Pilih bagian unit kerja pegawai'),

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
                                    ->preload()
                                    ->placeholder('Pilih sub bagian')
                                    ->disabled(fn(Forms\Get $get) => !$get('departments_id'))
                                    ->dehydrated(fn(Forms\Get $get) => filled($get('departments_id')))
                                    ->helperText('Akan muncul setelah memilih Bagian unit kerja'),
                            ]),
                        Forms\Components\Select::make('employee_position_id')
                            ->label('Jabatan')
                            ->options(MasterEmployeePosition::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih jabatan pegawai')
                            ->helperText('Jabatan resmi pegawai di perusahaan'),
                    ]),

                // Section tersembunyi untuk user yang login (opsional ditampilkan kalau perlu)
                Forms\Components\Hidden::make('users_id')
                    ->default(fn() => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nippam')
                    ->label('NIPPAM')
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

                Tables\Filters\SelectFilter::make('employee_position_id')
                    ->label('Jabatan')
                    ->options(MasterEmployeePosition::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->button()
                    ->label('Action'),
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
