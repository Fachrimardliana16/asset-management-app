<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use App\Models\MasterDepartments;
use App\Models\MasterSubDepartments;
use App\Models\MasterEmployeePosition;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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
                                    //->placeholder('Contoh: 12345678')
                                    ->helperText('Nomor Induk Pegawai PAM (unik, tidak boleh sama dengan yang lain)'),
                                //->rules(['regex:/^\d{8,20}$/']),
                                // ->validationMessages([
                                //     'regex' => 'NIPPAM harus berupa angka minimal 8 digit.',
                                //     'unique' => 'NIPPAM ini sudah digunakan oleh pegawai lain.',
                                // ]),

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
                            ->relationship('position', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih jabatan pegawai')
                            ->helperText('Pilih jabatan pegawai di perusahaan atau tambah jabatan baru')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Jabatan')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(MasterEmployeePosition::class, 'name')
                                    ->placeholder('Contoh: Manager, Staff, dll')
                                    ->helperText('Masukkan nama jabatan'),
                                Forms\Components\Textarea::make('desc')
                                    ->label('Deskripsi')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->placeholder('Masukkan deskripsi jabatan (opsional)')
                                    ->helperText('Deskripsi tentang jabatan ini'),
                                Forms\Components\Hidden::make('users_id')
                                    ->default(fn() => auth()->id()),
                            ])
                            ->createOptionUsing(function (array $data): string {
                                $position = MasterEmployeePosition::create([
                                    'name' => $data['name'],
                                    'desc' => $data['desc'] ?? null,
                                    'users_id' => $data['users_id'] ?? auth()->id(),
                                ]);

                                return $position->id;
                            }),
                    ]),

                Forms\Components\Section::make('Akun User')
                    ->description('Hubungkan pegawai dengan akun user yang ada. Bagian ini opsional.')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Forms\Components\Select::make('users_id')
                            ->label('User')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih user atau biarkan kosong')
                            ->helperText('Pilih user yang akan dihubungkan dengan pegawai ini. Jika tidak dipilih, pegawai tidak akan terhubung dengan akun user.')
                            ->getOptionLabelFromRecordUsing(fn(User $record): string => "{$record->username} ({$record->name})")
                            ->default(fn($operation) => $operation === 'create' ? null : null)
                            ->nullable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('username')
                                    ->label('Username')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(User::class, 'username')
                                    ->placeholder('Masukkan username'),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(User::class, 'email')
                                    ->placeholder('Masukkan email'),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('firstname')
                                            ->label('Nama Depan')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Nama depan'),
                                        Forms\Components\TextInput::make('lastname')
                                            ->label('Nama Belakang')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Nama belakang'),
                                    ]),
                                Forms\Components\Select::make('roles')
                                    ->label('Role')
                                    ->options(function () {
                                        return Role::all()->mapWithKeys(function ($role) {
                                            return [$role->id => Str::headline($role->name)];
                                        })->toArray();
                                    })
                                    ->multiple()
                                    ->preload()
                                    ->maxItems(1)
                                    ->native(false)
                                    ->required()
                                    ->helperText('Pilih role untuk user ini'),
                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->required()
                                    ->minLength(8)
                                    ->revealable()
                                    ->placeholder('Minimal 8 karakter'),
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Konfirmasi Password')
                                    ->password()
                                    ->required()
                                    ->same('password')
                                    ->revealable()
                                    ->placeholder('Ulangi password'),
                            ])
                            ->createOptionUsing(function (array $data): string {
                                $user = User::create([
                                    'username' => $data['username'],
                                    'email' => $data['email'],
                                    'firstname' => $data['firstname'],
                                    'lastname' => $data['lastname'],
                                    'password' => Hash::make($data['password']),
                                    'email_verified_at' => now(),
                                ]);

                                // Assign role jika ada
                                if (isset($data['roles']) && !empty($data['roles'])) {
                                    // Pastikan roles adalah array
                                    $roleIds = is_array($data['roles']) ? $data['roles'] : [$data['roles']];

                                    // Ambil role berdasarkan ID, lalu ambil name-nya
                                    $roles = Role::whereIn('id', $roleIds)->pluck('name')->toArray();

                                    if (!empty($roles)) {
                                        $user->syncRoles($roles);
                                    }
                                }

                                return $user->id;
                            }),
                    ]),
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

                Tables\Columns\TextColumn::make('user.username')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Tidak ada user')
                    ->icon('heroicon-o-user')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'gray')
                    ->formatStateUsing(fn($state) => $state ?? 'Tidak ada user'),

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
