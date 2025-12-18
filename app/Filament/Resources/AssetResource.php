<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Filament\Resources\AssetResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\AssetMaintenance;
use App\Models\AssetMutation;
use App\Models\Employee;
use App\Models\MasterAssetsCondition;
use App\Models\MasterAssetsLocation;
use App\Models\MasterAssetsStatus;
use App\Models\MasterAssetsSubLocation;
use App\Models\MasterAssetsTransactionStatus;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Asset';
    protected static ?string $navigationLabel = 'Data Aset';
    protected static ?string $modelLabel = 'Data Aset';
    protected static ?string $pluralModelLabel = 'Data Aset';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([

                // 1. Informasi Dasar Aset
                Section::make('Informasi Dasar Aset')
                    ->icon('heroicon-o-tag')
                    ->description('Data identitas utama aset')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('assets_number')
                                ->label('Nomor Aset')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Contoh: AST-2025-001'),

                            TextInput::make('name')
                                ->label('Nama Aset')
                                ->required()
                                ->maxLength(255),
                        ]),

                        Select::make('category_id')
                            ->relationship('categoryAsset', 'name')
                            ->label('Kategori Aset')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                    ]),

                // 2. Status & Kondisi
                Section::make('Status dan Kondisi')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status_id')
                                ->relationship('assetsStatus', 'name')
                                ->label('Status Aset')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('condition_id')
                                ->relationship('conditionAsset', 'name')
                                ->label('Kondisi Fisik')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('transaction_status_id')
                                ->relationship('AssetTransactionStatus', 'name')
                                ->label('Status Transaksi')
                                ->hidden()
                                ->dehydrated(fn($state) => filled($state)),
                        ]),
                    ]),

                // 3. Data Pembelian
                Section::make('Data Pembelian')
                    ->icon('heroicon-o-shopping-cart')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)->schema([
                            DatePicker::make('purchase_date')
                                ->label('Tanggal Pembelian')
                                ->required()
                                ->default(today())
                                ->maxDate(today()),

                            TextInput::make('price')
                                ->label('Harga Beli')
                                ->numeric()
                                ->prefix('Rp ')
                                ->inputMode('decimal')
                                ->rules(['regex:/^\d{1,15}$/'])
                                ->placeholder('Tanpa titik atau koma'),

                            TextInput::make('funding_source')
                                ->label('Sumber Dana')
                                ->required()
                                ->placeholder('Contoh : RKA 2025')
                                ->maxLength(255),
                        ]),

                        TextInput::make('brand')
                            ->label('Merk')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                // 4. Data Akuntansi
                Section::make('Nilai Buku & Penyusutan')
                    ->icon('heroicon-o-calculator')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('book_value')
                                ->label('Nilai Buku Saat Ini')
                                ->numeric()
                                ->prefix('Rp ')
                                ->helperText('Opsional, isi jika sudah ada penyusutan'),

                            DatePicker::make('book_value_expiry')
                                ->label('Tanggal Habis Nilai Buku')
                                ->minDate(fn($get) => $get('purchase_date') ?? today()),
                        ]),
                    ]),

                // 5. Deskripsi & Dokumentasi
                Section::make('Deskripsi dan Foto Aset')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Textarea::make('desc')
                            ->label('Deskripsi / Spesifikasi')
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('img')
                            ->label('Foto Aset')
                            ->image()
                            ->multiple()                              // tetap bisa banyak
                            ->maxFiles(5)
                            ->maxSize(5120)                           // 5MB per file
                            ->directory('assets')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])

                            // Yang bikin "gede" dihapus/dikecilin:
                            ->imagePreviewHeight('100')               // preview kecil & rapi
                            ->panelLayout('compact')                  // paling penting! ini yang bikin normal
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('center')

                            ->helperText('Maksimal 5 foto, ≤ 5MB, format JPG/PNG/WebP')
                            ->columnSpanFull(),
                    ]),

                // Hidden field
                Hidden::make('users_id')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Gambar + No (hover muncul created_at)
                Tables\Columns\ImageColumn::make('img')
                    ->label('Gambar')
                    ->size(60)
                    ->rounded()
                    ->defaultImageUrl(asset('images/no-image.png'))
                    ->extraImgAttributes(fn($record) => [
                        'title' => "No. " . $record->getKey() . "\nDibuat: " . $record->created_at->format('d/m/Y H:i')
                    ]),

                // 2. Nomor Aset + Nama + Merk (kolom utama)
                Tables\Columns\TextColumn::make('assets_number')
                    ->label('Nomor Aset & Nama')
                    ->description(fn($record) => $record->name, position: 'above')
                    ->description(fn($record) => $record->brand, position: 'below')
                    ->searchable(['assets_number', 'name', 'brand'])
                    ->sortable()
                    ->wrap(),

                // 3. Kategori + Kondisi + Status (pakai description & badge)
                Tables\Columns\TextColumn::make('categoryAsset.name')
                    ->label('Info Aset')
                    ->description(fn($record) => $record->conditionAsset?->name, position: 'above')
                    ->description(fn($record) => $record->assetsStatus?->name, position: 'below')
                    ->sortable(),

                // 4. Pemegang / Lokasi Terakhir
                Tables\Columns\TextColumn::make('latest_holder')
                    ->label('Pemegang')
                    ->getStateUsing(
                        fn($record) =>
                        $record->latestMutation?->AssetsMutationemployee?->name ??
                            $record->latestMutation?->AssetsMutationlocation?->name ??
                            'Di Gudang'
                    )
                    ->color(fn($state) => $state === 'Di Gudang' ? 'gray' : 'success')
                    // ->italic(fn($state) => $state === 'Di Gudang')
                    ->placeholder('-')
                    ->limit(30),

                // 5. Harga + Sumber Dana
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('Rp ')
                    ->sortable()
                    ->description(fn($record) => $record->funding_source, position: 'below'),

                // 6. Tanggal Pembelian + Habis Buku
                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Tanggal Beli')
                    ->date('d M Y')
                    ->sortable()
                    ->description(
                        fn($record) =>
                        $record->book_value_expiry?->format('d M Y') ?? '−',
                        position: 'below'
                    )
                    ->alignCenter(),

                // 7. Status Mutasi (badge kecil)
                Tables\Columns\TextColumn::make('AssetTransactionStatus.name')
                    ->label('Mutasi')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Transaksi Keluar' => 'danger',
                        'Transaksi Masuk'  => 'success',
                        default => 'gray',
                    })
                    ->placeholder('Di Gudang')
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    // 1. Lihat Detail
                    Tables\Actions\ViewAction::make(),

                    // 2. Edit Aset
                    Tables\Actions\EditAction::make(),

                    // 3. Print Stiker
                    Action::make('print_stiker')
                        ->label('Print Stiker')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn(Asset $record) => route('asset.print-barcode', $record->id))
                        ->openUrlInNewTab(),

                    // 4. MUTASI ASET
                    Tables\Actions\Action::make('mutasi')
                        ->label('Mutasi Aset')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('warning')
                        ->visible(function (Asset $record) {
                            // Aset harus active
                            if (!in_array($record->assetsStatus?->name, ['Active', 'Aktif'])) {
                                return false;
                            }

                            // Cek apakah ada transaksi yang bisa dilakukan
                            $keluarCount = AssetMutation::where('assets_id', $record->id)
                                ->whereHas('AssetsMutationtransactionStatus', fn($q) => $q->where('name', 'Transaksi Keluar'))
                                ->count();

                            $masukCount = AssetMutation::where('assets_id', $record->id)
                                ->whereHas('AssetsMutationtransactionStatus', fn($q) => $q->where('name', 'Transaksi Masuk'))
                                ->count();

                            // Jika keluar > masuk, maka hanya bisa masuk
                            // Jika keluar <= masuk, maka hanya bisa keluar
                            // Selalu tampilkan button karena pasti ada satu opsi yang bisa dipilih
                            return true;
                        })
                        ->modalHeading(fn(Asset $record) => 'Mutasi Aset: ' . $record->assets_number)
                        ->modalDescription(function (Asset $record) {
                            $keluarCount = AssetMutation::where('assets_id', $record->id)
                                ->whereHas('AssetsMutationtransactionStatus', fn($q) => $q->where('name', 'Transaksi Keluar'))
                                ->count();

                            $masukCount = AssetMutation::where('assets_id', $record->id)
                                ->whereHas('AssetsMutationtransactionStatus', fn($q) => $q->where('name', 'Transaksi Masuk'))
                                ->count();

                            $isCurrentlyOut = $keluarCount > $masukCount;

                            if ($isCurrentlyOut) {
                                return 'Aset sedang di luar (dipinjam/dimutasi). Hanya bisa melakukan Transaksi Masuk.';
                            } else {
                                return 'Aset berada di gudang. Hanya bisa melakukan Transaksi Keluar.';
                            }
                        })
                        ->modalSubmitActionLabel('Simpan Mutasi')
                        ->form(function (Asset $record) {
                            $keluarCount = AssetMutation::where('assets_id', $record->id)
                                ->whereHas('AssetsMutationtransactionStatus', fn($q) => $q->where('name', 'Transaksi Keluar'))
                                ->count();

                            $masukCount = AssetMutation::where('assets_id', $record->id)
                                ->whereHas('AssetsMutationtransactionStatus', fn($q) => $q->where('name', 'Transaksi Masuk'))
                                ->count();

                            $isCurrentlyOut = $keluarCount > $masukCount;

                            // Tentukan jenis mutasi yang bisa dipilih
                            if ($isCurrentlyOut) {
                                // Aset sedang di luar, hanya bisa masuk
                                $transactionOptions = MasterAssetsTransactionStatus::where('name', 'Transaksi Masuk')->pluck('name', 'id');
                                $defaultTransactionId = MasterAssetsTransactionStatus::where('name', 'Transaksi Masuk')->first()?->id;
                            } else {
                                // Aset di gudang, hanya bisa keluar
                                $transactionOptions = MasterAssetsTransactionStatus::where('name', 'Transaksi Keluar')->pluck('name', 'id');
                                $defaultTransactionId = MasterAssetsTransactionStatus::where('name', 'Transaksi Keluar')->first()?->id;
                            }

                            return [
                                Forms\Components\TextInput::make('mutations_number')
                                    ->label('Nomor Mutasi')
                                    ->required()
                                    ->default(fn() => 'MUT-' . date('YmdHis'))
                                    ->placeholder('MUT-001'),

                                Forms\Components\DatePicker::make('mutation_date')
                                    ->label('Tanggal Mutasi')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\Select::make('transaction_status_id')
                                    ->label('Jenis Mutasi')
                                    ->options($transactionOptions)
                                    ->required()
                                    ->default($defaultTransactionId)
                                    ->disabled()
                                    ->helperText($isCurrentlyOut
                                        ? 'Aset sedang di luar, hanya bisa Transaksi Masuk'
                                        : 'Aset di gudang, hanya bisa Transaksi Keluar'),

                                Forms\Components\Select::make('employees_id')
                                    ->label('Pemegang/Penanggung Jawab')
                                    ->options(Employee::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\Select::make('location_id')
                                    ->label('Lokasi Tujuan')
                                    ->options(MasterAssetsLocation::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\Select::make('sub_location_id')
                                    ->label('Sub Lokasi')
                                    ->options(MasterAssetsSubLocation::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\Textarea::make('desc')
                                    ->label('Catatan Mutasi')
                                    ->rows(3)
                                    ->placeholder('Contoh: Dipindahkan untuk keperluan proyek X'),
                            ];
                        })
                        ->action(function (Asset $record, array $data) {
                            // Re-validasi di action untuk keamanan
                            $keluarCount = AssetMutation::where('assets_id', $record->id)
                                ->whereHas('AssetsMutationtransactionStatus', fn($q) => $q->where('name', 'Transaksi Keluar'))
                                ->count();

                            $masukCount = AssetMutation::where('assets_id', $record->id)
                                ->whereHas('AssetsMutationtransactionStatus', fn($q) => $q->where('name', 'Transaksi Masuk'))
                                ->count();

                            $isCurrentlyOut = $keluarCount > $masukCount;

                            // Tentukan transaction_status_id yang benar berdasarkan status aset
                            if ($isCurrentlyOut) {
                                $transactionStatusId = MasterAssetsTransactionStatus::where('name', 'Transaksi Masuk')->first()?->id;
                            } else {
                                $transactionStatusId = MasterAssetsTransactionStatus::where('name', 'Transaksi Keluar')->first()?->id;
                            }

                            $newMutation = DB::transaction(function () use ($record, $data, $transactionStatusId) {
                                // Simpan ke tabel asset_mutations
                                $mutation = AssetMutation::create([
                                    'mutations_number' => $data['mutations_number'],
                                    'mutation_date' => $data['mutation_date'],
                                    'transaction_status_id' => $transactionStatusId,
                                    'assets_id' => $record->id,
                                    'assets_number' => $record->assets_number,
                                    'name' => $record->name,
                                    'condition_id' => $record->condition_id,
                                    'employees_id' => $data['employees_id'],
                                    'location_id' => $data['location_id'],
                                    'sub_location_id' => $data['sub_location_id'],
                                    'desc' => $data['desc'] ?? null,
                                    'users_id' => auth()->id(),
                                ]);

                                // Update status transaksi aset
                                $record->update([
                                    'transaction_status_id' => $transactionStatusId
                                ]);

                                return $mutation;
                            });

                            $transactionType = $isCurrentlyOut ? 'Masuk' : 'Keluar';

                            Notification::make()
                                ->success()
                                ->title('Sukses!')
                                ->body("Aset berhasil dimutasi (Transaksi {$transactionType}).")
                                ->actions([
                                    NotificationAction::make('cetak_doc')
                                        ->label('Cetak Doc Serah Terima')
                                        ->icon('heroicon-o-printer')
                                        ->button()
                                        ->color('primary')
                                        ->url(route('assets.cetak-serah-terima', $newMutation->id))
                                        ->openUrlInNewTab(),
                                ])
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-arrow-path')
                        ->modalIconColor('warning'),

                    // 5. PEMELIHARAAN ASET
                    Tables\Actions\Action::make('pemeliharaan')
                        ->label('Pemeliharaan')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->color('info')
                        ->visible(fn(Asset $record) => $record->assetsStatus?->name === 'Active' || $record->assetsStatus?->name === 'Aktif')
                        ->modalHeading(fn(Asset $record) => 'Pemeliharaan Aset: ' . $record->assets_number)
                        ->modalDescription('Input data pemeliharaan/perbaikan aset')
                        ->modalSubmitActionLabel('Simpan Pemeliharaan')
                        ->form([
                            Forms\Components\DatePicker::make('maintenance_date')
                                ->label('Tanggal Pemeliharaan')
                                ->default(now())
                                ->required(),

                            Forms\Components\Select::make('service_type')
                                ->label('Jenis Perbaikan')
                                ->options([
                                    'Perbaikan Ringan' => 'Perbaikan Ringan',
                                    'Perbaikan Sedang' => 'Perbaikan Sedang',
                                    'Perbaikan Berat' => 'Perbaikan Berat',
                                    'Perawatan Berkala' => 'Perawatan Berkala',
                                ])
                                ->required()
                                ->native(false),

                            Forms\Components\TextInput::make('location_service')
                                ->label('Lokasi Service/Tempat Perbaikan')
                                ->required()
                                ->placeholder('Contoh: Bengkel ABC, Workshop Internal'),

                            Forms\Components\TextInput::make('service_cost')
                                ->label('Biaya Perbaikan')
                                ->prefix('Rp')
                                ->required()
                                ->numeric()
                                ->default(0),

                            Forms\Components\Textarea::make('desc')
                                ->label('Deskripsi Kerusakan & Perbaikan')
                                ->rows(3)
                                ->placeholder('Jelaskan kerusakan dan tindakan perbaikan...'),
                        ])
                        ->action(function (Asset $record, array $data) {
                            AssetMaintenance::create([
                                'maintenance_date' => $data['maintenance_date'],
                                'assets_id' => $record->id,
                                'service_type' => $data['service_type'],
                                'location_service' => $data['location_service'],
                                'service_cost' => $data['service_cost'],
                                'desc' => $data['desc'] ?? null,
                                'users_id' => auth()->id(),
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Sukses!')
                                ->body('Data pemeliharaan berhasil disimpan.')
                                ->send();
                        })
                        ->modalIcon('heroicon-o-wrench-screwdriver')
                        ->modalIconColor('info'),

                    // 6. PENGHAPUSAN ASET
                    Tables\Actions\Action::make('penghapusan')
                        ->label('Penghapusan')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->visible(fn(Asset $record) => $record->assetsStatus?->name === 'Active' || $record->assetsStatus?->name === 'Aktif')
                        ->modalHeading(fn(Asset $record) => 'Penghapusan Aset: ' . $record->assets_number)
                        ->modalDescription('Penghapusan akan mengubah status aset menjadi Inactive')
                        ->modalSubmitActionLabel('Proses Penghapusan')
                        ->form([
                            Forms\Components\TextInput::make('disposals_number')
                                ->label('Nomor Penghapusan')
                                ->required()
                                ->default(fn() => 'DSP-' . date('YmdHis'))
                                ->placeholder('DSP-001'),

                            Forms\Components\DatePicker::make('disposal_date')
                                ->label('Tanggal Penghapusan')
                                ->default(now())
                                ->required(),

                            Forms\Components\TextInput::make('book_value')
                                ->label('Nilai Buku')
                                ->prefix('Rp')
                                ->required()
                                ->numeric()
                                ->default(fn(Asset $record) => $record->book_value ?? 0),

                            Forms\Components\TextInput::make('disposal_value')
                                ->label('Nilai Penghapusan/Penjualan')
                                ->prefix('Rp')
                                ->required()
                                ->numeric()
                                ->default(0),

                            Forms\Components\Textarea::make('disposal_reason')
                                ->label('Alasan Penghapusan')
                                ->required()
                                ->rows(3)
                                ->placeholder('Jelaskan alasan penghapusan...'),

                            Forms\Components\Select::make('disposal_process')
                                ->label('Proses Penghapusan')
                                ->options([
                                    'dimusnahkan' => 'Dimusnahkan',
                                    'dijual' => 'Dijual',
                                    'dihibahkan' => 'Dihibahkan',
                                    'dihapus dari inventaris' => 'Dihapus dari Inventaris',
                                ])
                                ->required()
                                ->native(false),

                            Forms\Components\Select::make('employee_id')
                                ->label('Pejabat yang Menyetujui')
                                ->options(Employee::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),

                            Forms\Components\Textarea::make('disposal_notes')
                                ->label('Catatan Tambahan')
                                ->rows(2)
                                ->placeholder('Catatan tambahan (opsional)...'),
                        ])
                        ->action(function (Asset $record, array $data) {
                            DB::transaction(function () use ($record, $data) {
                                // Simpan data penghapusan
                                AssetDisposal::create([
                                    'disposals_number' => $data['disposals_number'],
                                    'disposal_date' => $data['disposal_date'],
                                    'assets_id' => $record->id,
                                    'book_value' => $data['book_value'],
                                    'disposal_value' => $data['disposal_value'],
                                    'disposal_reason' => $data['disposal_reason'],
                                    'disposal_process' => $data['disposal_process'],
                                    'employee_id' => $data['employee_id'],
                                    'disposal_notes' => $data['disposal_notes'] ?? null,
                                    'users_id' => auth()->id(),
                                ]);

                                // Update status aset menjadi Inactive
                                $inactiveStatus = MasterAssetsStatus::where('name', 'Inactive')->first();
                                if ($inactiveStatus) {
                                    $record->update(['status_id' => $inactiveStatus->id]);
                                }
                            });

                            Notification::make()
                                ->success()
                                ->title('Sukses!')
                                ->body('Aset berhasil dihapus dari inventaris.')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->modalIconColor('danger'),

                    // 7. Hapus Record
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation(),

                ])
                    ->label('Action')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('primary')
                    ->button()
                    ->size('md'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('print_labels')
                        ->label('Print Label Massal')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function ($records) {
                            $ids = $records->pluck('id')->toArray();
                            $url = route('asset.print-barcode-bulk', ['ids' => implode(',', $ids)]);

                            // Open in new tab
                            return redirect()->to($url);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(false),
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
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'view' => Pages\ViewAsset::route('/{record}'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }

    /**
     * Add eager loading to prevent N+1 query issues
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'categoryAsset',
                'conditionAsset',
                'assetsStatus',
                'AssetTransactionStatus',
                'latestMutation.AssetsMutationemployee',
                'latestMutation.AssetsMutationlocation',
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Aset berhasil ditambahkan!')
            ->success()
            ->send();
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Aset berhasil diperbarui!')
            ->success()
            ->send();
    }
}
