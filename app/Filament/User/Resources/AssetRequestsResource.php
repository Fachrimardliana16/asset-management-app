<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\AssetRequestsResource\Pages;
use App\Models\AssetRequests;
use App\Models\MasterCabangUnit;
use App\Models\MasterSubDepartments;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Layout;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn; // optional kalau mau
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class AssetRequestsResource extends Resource
{
    protected static ?string $model = AssetRequests::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Asset';
    protected static ?string $navigationLabel = 'Permintaan Barang';
    protected static ?string $modelLabel = 'Permintaan Barang';
    protected static ?string $pluralModelLabel = 'Permintaan Barang';
    protected static ?int $navigationSort = 1;

    private static function getPendingCount(): int
    {
        static $count = null;
        if ($count === null) {
            $count = (int) static::getModel()::where('purchase_status', 'pending')->count();
        }
        return $count;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getPendingCount();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getPendingCount() > 0 ? 'warning' : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'items.category',
                'items.purchases',
                'department',
                'subDepartment',
                'cabangUnit',
                'requestedBy',
                'user'
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Umum Permintaan')
                    ->description('Data umum permintaan barang')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('document_number')
                                    ->label('Nomor DBP')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->default(fn() => 'PR-' . now()->format('Ym') . '-' . str_pad(AssetRequests::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count() + 1, 4, '0', STR_PAD_LEFT))
                                    ->disabledOn('edit')
                                    ->dehydrated(),

                                Forms\Components\DatePicker::make('date')
                                    ->label('Tanggal Permintaan')
                                    ->required()
                                    ->default(now())
                                    ->validationMessages([
                                        'required' => 'Tanggal permintaan wajib diisi.',
                                    ]),

                                Forms\Components\Radio::make('request_origin_type')
                                    ->label('Asal Permintaan')
                                    ->options([
                                        'pusat'  => 'Kantor Pusat',
                                        'cabang' => 'Cabang & Unit',
                                    ])
                                    ->default('pusat')
                                    ->inline()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set) {
                                        $set('department_id', null);
                                        $set('sub_department_id', null);
                                        $set('cabang_unit_id', null);
                                        $set('requested_by_pusat', null);
                                        $set('requested_by_cabang', null);
                                        $set('requested_by', null);
                                    })
                                    ->helperText('Pilih apakah permintaan dari Kantor Pusat atau Cabang & Unit')
                                    ->columnSpanFull(),

                                // === KANTOR PUSAT ===
                                Forms\Components\Select::make('department_id')
                                    ->relationship('department', 'name')
                                    ->label('Bagian / Departemen')
                                    ->searchable()
                                    ->preload()
                                    ->required(fn (Get $get) => $get('request_origin_type') === 'pusat')
                                    ->visible(fn (Get $get) => $get('request_origin_type') === 'pusat')
                                    ->live()
                                    ->afterStateUpdated(function(Forms\Set $set) {
                                        $set('sub_department_id', null);
                                        $set('requested_by_pusat', null);
                                    })
                                    ->helperText('Pilih bagian/departemen pemohon'),

                                Forms\Components\Select::make('sub_department_id')
                                    ->label('Sub Bagian')
                                    ->options(function (Get $get) {
                                        $departmentId = $get('department_id');
                                        if (!$departmentId) {
                                            return [];
                                        }
                                        return MasterSubDepartments::where('departments_id', $departmentId)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Get $get) => $get('request_origin_type') === 'pusat')
                                    ->disabled(fn(Get $get) => !$get('department_id'))
                                    ->live()
                                    ->afterStateUpdated(fn(Forms\Set $set) => $set('requested_by_pusat', null))
                                    ->helperText('Pilih sub bagian asal permintaan'),

                                Forms\Components\Select::make('requested_by_pusat')
                                    ->label('Pemohon')
                                    ->options(function (Get $get) {
                                        $deptId = $get('department_id');
                                        $subDeptId = $get('sub_department_id');
                                        
                                        if (!$deptId) return [];
                                        
                                        $query = \App\Models\Employee::where('departments_id', $deptId)
                                            ->where('work_location_type', 'pusat');
                                            
                                        if ($subDeptId) {
                                            $query->where('sub_department_id', $subDeptId);
                                        }
                                            
                                        return $query->orderBy('name')->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload(false)
                                    ->required(fn (Get $get) => $get('request_origin_type') === 'pusat')
                                    ->visible(fn (Get $get) => $get('request_origin_type') === 'pusat')
                                    ->disabled(fn (Get $get) => $get('request_origin_type') === 'pusat' && !$get('department_id'))
                                    ->placeholder('Pilih bagian terlebih dahulu...')
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function(Forms\Components\Select $component, $state, $record) {
                                        if ($record && $record->request_origin_type === 'pusat') {
                                            $component->state($record->requested_by);
                                        }
                                    }),

                                // === CABANG & UNIT ===
                                Forms\Components\Select::make('cabang_unit_id')
                                    ->label('Cabang & Unit')
                                    ->options(MasterCabangUnit::pluck('nama', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(fn (Get $get) => $get('request_origin_type') === 'cabang')
                                    ->visible(fn (Get $get) => $get('request_origin_type') === 'cabang')
                                    ->live()
                                    ->afterStateUpdated(fn(Forms\Set $set) => $set('requested_by_cabang', null))
                                    ->helperText('Pilih cabang atau unit asal permintaan'),

                                Forms\Components\Select::make('requested_by_cabang')
                                    ->label('Pemohon')
                                    ->options(function (Get $get) {
                                        $cabangId = $get('cabang_unit_id');
                                        if (!$cabangId) return [];
                                        return \App\Models\Employee::where('cabang_unit_id', $cabangId)
                                            ->where('work_location_type', 'cabang')
                                            ->orderBy('name')
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload(false)
                                    ->required(fn (Get $get) => $get('request_origin_type') === 'cabang')
                                    ->visible(fn (Get $get) => $get('request_origin_type') === 'cabang')
                                    ->disabled(fn (Get $get) => !$get('cabang_unit_id'))
                                    ->placeholder('Pilih cabang terlebih dahulu...')
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function(Forms\Components\Select $component, $state, $record) {
                                        if ($record && $record->request_origin_type === 'cabang') {
                                            $component->state($record->requested_by);
                                        }
                                    }),
                            ]),

                        Forms\Components\Hidden::make('requested_by')
                            ->dehydrateStateUsing(function ($state, Forms\Get $get) {
                                if ($get('request_origin_type') === 'pusat') {
                                    return $get('requested_by_pusat');
                                }
                                return $get('requested_by_cabang');
                            }),

                        Forms\Components\Textarea::make('desc')
                            ->label('Keterangan Umum')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Contoh: Masuk RKA 2025/Pengalihan RKA 2025'),
                    ])
                    ->collapsible(),

                Section::make('Daftar Barang yang Diminta')
                    ->description('Tambahkan satu atau lebih barang yang diminta')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('asset_name')
                                            ->label('Nama Barang')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Contoh: Laptop Dell Latitude 5420'),

                                        Forms\Components\Select::make('category_id')
                                            ->label('Kategori')
                                            ->options(\App\Models\MasterAssetsCategory::pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Jumlah')
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(1)
                                            ->suffix(' unit'),
                                    ]),

                                Forms\Components\TextInput::make('purpose')
                                    ->label('Keperluan')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Untuk staff baru'),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Catatan Item')
                                    ->rows(2)
                                    ->placeholder('Catatan khusus untuk item ini (opsional)'),
                            ])
                            ->columns(1)
                            ->itemLabel(fn(array $state): ?string => $state['asset_name'] ?? 'Item Baru')
                            ->addActionLabel('+ Tambah Barang')
                            ->collapsible()
                            ->collapsed(fn(string $operation) => $operation === 'edit')
                            ->cloneable()
                            ->reorderable()
                            ->minItems(1)
                            ->maxItems(20)
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Auto calculate totals
                                $totalItems = count($state ?? []);
                                $totalQuantity = collect($state ?? [])->sum('quantity');
                                $set('total_items', $totalItems);
                                $set('total_quantity', $totalQuantity);
                            })
                            ->live(),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\Placeholder::make('total_items')
                                    ->label('Total Jenis Barang')
                                    ->content(fn(Get $get) => count($get('items') ?? []) . ' jenis'),

                                Forms\Components\Placeholder::make('total_quantity')
                                    ->label('Total Unit')
                                    ->content(fn(Get $get) => collect($get('items') ?? [])->sum('quantity') . ' unit'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Pengesahan')
                    ->description('Pengesahan Permintaan')
                    ->columns(5)
                    ->schema([
                        Forms\Components\Toggle::make('kepala_sub_bagian')
                            ->label('Kepala Sub Bagian')
                            ->inline(false)
                            ->onIcon('heroicon-o-check-circle')
                            ->offIcon('heroicon-o-x-circle')
                            ->onColor('success')
                            ->offColor('danger'),

                        Forms\Components\Toggle::make('kepala_bagian_umum')
                            ->label('Kepala Bagian Umum')
                            ->inline(false)
                            ->onIcon('heroicon-o-check-circle')
                            ->offIcon('heroicon-o-x-circle')
                            ->onColor('success')
                            ->offColor('danger'),

                        Forms\Components\Toggle::make('kepala_bagian_keuangan')
                            ->label('Kepala Bagian Keuangan')
                            ->inline(false)
                            ->onIcon('heroicon-o-check-circle')
                            ->offIcon('heroicon-o-x-circle')
                            ->onColor('success')
                            ->offColor('danger'),

                        Forms\Components\Toggle::make('direktur_umum')
                            ->label('Direktur Umum')
                            ->inline(false)
                            ->onIcon('heroicon-o-check-circle')
                            ->offIcon('heroicon-o-x-circle')
                            ->onColor('success')
                            ->offColor('danger'),

                        Forms\Components\Toggle::make('direktur_utama')
                            ->label('Direktur Utama')
                            ->inline(false)
                            ->onIcon('heroicon-o-check-circle')
                            ->offIcon('heroicon-o-x-circle')
                            ->onColor('success')
                            ->offColor('danger'),

                        Forms\Components\FileUpload::make('docs')
                            ->label('Bukti Lampiran')
                            ->directory('bukti-permintaan')
                            ->disk('public')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1'
                            ])
                            ->maxSize(5120) // 5MB
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->helperText('Maksimal 5MB. Format: JPG, PNG, PDF')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('users_id')
                            ->default(auth()->id()),
                    ]),

                Section::make('Informasi Pembuat')
                    ->schema([
                        Forms\Components\Placeholder::make('created_by')
                            ->label('Diajukan Oleh')
                            ->content(fn($record) => $record?->user ? $record->user->firstname . ' ' . $record->user->lastname : (auth()->user()->firstname . ' ' . auth()->user()->lastname)),

                        Forms\Components\Placeholder::make('created_at')
                            ->label('Tanggal Diajukan')
                            ->content(fn($record) => $record?->created_at?->format('d M Y H:i') ?? now()->format('d M Y H:i')),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_number')
                    ->label('Nomor DBP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                // Asal Permintaan
                Tables\Columns\BadgeColumn::make('request_origin_type')
                    ->label('Asal')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pusat'  => 'Kantor Pusat',
                        'cabang' => 'Cabang & Unit',
                        default  => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'pusat'  => 'info',
                        'cabang' => 'warning',
                        default  => 'gray',
                    })
                    ->sortable(),

                // Total Items & Quantity
                Tables\Columns\TextColumn::make('total_items')
                    ->label('Jml Jenis')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Jml Unit')
                    ->alignCenter()
                    ->sortable(),

                // Department (Pusat)
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Bagian')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subDepartment.name')
                    ->label('Sub Bagian')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                // Cabang & Unit
                Tables\Columns\TextColumn::make('cabangUnit.nama')
                    ->label('Cabang & Unit')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                // Pemohon
                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Pemohon')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                // Status Pembelian
                Tables\Columns\TextColumn::make('purchase_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'Menunggu',
                        'in_progress' => 'Diproses',
                        'purchased' => 'Dibeli',
                        'cancelled' => 'Dibatalkan',
                        default => 'Menunggu',
                    })
                    ->color(fn($state) => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'purchased' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\BooleanColumn::make('status_request')
                    ->label('Approved')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\ImageColumn::make('docs')
                    ->label('Lampiran')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('request_origin_type')
                    ->label('Asal Permintaan')
                    ->options([
                        'pusat'  => 'Kantor Pusat',
                        'cabang' => 'Cabang & Unit',
                    ]),

                Tables\Filters\SelectFilter::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Bagian')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('sub_department_id')
                    ->relationship('subDepartment', 'name')
                    ->label('Sub Bagian')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('cabang_unit_id')
                    ->label('Cabang & Unit')
                    ->options(MasterCabangUnit::pluck('nama', 'id'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('purchase_status')
                    ->label('Status Pembelian')
                    ->options([
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Diproses',
                        'purchased' => 'Sudah Dibeli',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\Filter::make('not_approved')
                    ->label(function () {
                        $count = \App\Models\AssetRequests::where(function ($q) {
                            $q->where('kepala_sub_bagian', false)
                                ->orWhereNull('kepala_sub_bagian')
                                ->orWhere('kepala_bagian_umum', false)
                                ->orWhereNull('kepala_bagian_umum')
                                ->orWhere('kepala_bagian_keuangan', false)
                                ->orWhereNull('kepala_bagian_keuangan')
                                ->orWhere('direktur_umum', false)
                                ->orWhereNull('direktur_umum')
                                ->orWhere('direktur_utama', false)
                                ->orWhereNull('direktur_utama')
                                ->orWhere('status_request', false)
                                ->orWhereNull('status_request');
                        })->count();
                        return "Belum Approve ({$count})";
                    })
                    ->query(function (Builder $query): Builder {
                        return $query->where(function ($q) {
                            $q->where('kepala_sub_bagian', false)
                                ->orWhereNull('kepala_sub_bagian')
                                ->orWhere('kepala_bagian_umum', false)
                                ->orWhereNull('kepala_bagian_umum')
                                ->orWhere('kepala_bagian_keuangan', false)
                                ->orWhereNull('kepala_bagian_keuangan')
                                ->orWhere('direktur_umum', false)
                                ->orWhereNull('direktur_umum')
                                ->orWhere('direktur_utama', false)
                                ->orWhereNull('direktur_utama')
                                ->orWhere('status_request', false)
                                ->orWhereNull('status_request');
                        });
                    })
                    ->indicator('Belum Approve'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Berhasil!')
                                ->body('Data permintaan barang berhasil dihapus.')
                        ),
                ])
                    ->label('Action')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('primary')
                    ->button(), // Tampil sebagai tombol "Action" yang rapi
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Berhasil!')
                                ->body('Data permintaan barang yang dipilih berhasil dihapus.')
                        ),
                ]),
            ])
            ->emptyStateHeading('Belum ada data permintaan barang')
            ->emptyStateDescription('Silakan tambah data permintaan barang dengan klik tombol di bawah.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssetRequests::route('/'),
            'create' => Pages\CreateAssetRequests::route('/create'),
            'view'   => Pages\ViewAssetRequests::route('/{record}'),
            'edit'   => Pages\EditAssetRequests::route('/{record}/edit'),
        ];
    }
}
