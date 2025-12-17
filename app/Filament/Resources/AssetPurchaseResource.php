<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetPurchaseResource\Pages;
use App\Filament\Resources\AssetPurchaseResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetPurchase;
use App\Models\AssetRequests;
use App\Models\MasterAssetsCondition;
use App\Models\MasterAssetsStatus;
use App\Services\AssetNumberGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;

class AssetPurchaseResource extends Resource
{
    protected static ?string $model = AssetRequests::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Asset';
    protected static ?string $navigationLabel = 'Pembelian Barang';
    protected static ?string $modelLabel = 'Pembelian Barang';
    protected static ?string $pluralModelLabel = 'Pembelian Barang';
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Permintaan Barang')
                    ->description('Data Permintaan yang akan diproses')
                    ->schema([
                        Forms\Components\TextInput::make('document_number')
                            ->label('Nomor Dokumen')
                            ->disabled(),
                        Forms\Components\TextInput::make('asset_name')
                            ->label('Nama Barang')
                            ->disabled(),
                        Forms\Components\TextInput::make('category.name')
                            ->label('Kategori Barang')
                            ->disabled(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah')
                            ->disabled(),
                        Forms\Components\TextInput::make('purpose')
                            ->label('Keperluan')
                            ->disabled(),
                        Forms\Components\Textarea::make('desc')
                            ->label('Keterangan')
                            ->disabled(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with('purchases'))
            ->columns([
                // 1. No. DBP & Tanggal
                TextColumn::make('document_number')
                    ->label('Info DPB')
                    ->description(fn($record) => $record->date->format('d M Y'))
                    ->searchable()
                    ->sortable(),

                // 2. Barang (Nama Barang + Kategori + Jumlah)
                TextColumn::make('asset_name')
                    ->label('Detail Barang')
                    ->description(
                        fn($record) => ($record->category?->name ?? '-') . "\n" .
                            $record->quantity . ' unit'
                    )
                    ->searchable(['asset_name', 'category.name']),

                // 3. Pemohon + Keperluan
                TextColumn::make('employee.name')
                    ->label('Pemohon & Keperluan')
                    ->iconColor('gray') // mirip text-gray-600 di icon mu
                    ->description(fn($record) => \Illuminate\Support\Str::limit($record->purpose ?? '', 60, '...'))
                    ->tooltip(fn($record) => $record->purpose ?? '-')
                    ->searchable(['employee.name', 'purpose']),

                // 4. Lokasi + Sub Lokasi
                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->description(
                        fn($record) =>
                        $record->subLocation?->name ??
                            $record->masterAssetsSubLocation?->name ??
                            '–'
                    )
                    ->searchable(['location.name', 'subLocation.name']),

                // 5. Total Harga (jika sudah dibeli)
                TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->state(function ($record) {
                        if ($record->purchase_status === 'purchased' && $record->purchases->isNotEmpty()) {
                            $purchase   = $record->purchases->first();
                            $totalPrice = $purchase->price * $record->quantity;
                            return 'Rp ' . number_format($totalPrice, 0, ',', '.');
                        }

                        return 'Belum dibeli';
                    })
                    ->description(function ($record) {
                        if ($record->purchase_status === 'purchased' && $record->purchases->isNotEmpty()) {
                            $purchase = $record->purchases->first();
                            return '@Rp ' . number_format($purchase->price, 0, ',', '.') . ' × ' . $record->quantity . ' unit';
                        }

                        return null;
                    })
                    ->extraAttributes(
                        fn($record) =>
                        $record->purchase_status === 'purchased' && $record->purchases->isNotEmpty()
                            ? ['class' => 'text-green-600 font-medium']
                            : ['class' => 'text-xs italic text-gray-400']
                    ),

                Tables\Columns\TextColumn::make('purchase_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Diproses',
                        'purchased' => 'Sudah Dibeli',
                        'cancelled' => 'Dibatalkan',
                        default => 'Menunggu',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'purchased' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),

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
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori')
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
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    // 1. Action Utama: Selesai Dibeli (Modal Form Lengkap)
                    Tables\Actions\Action::make('selesai_dibeli')
                        ->label('Selesai Dibeli')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(AssetRequests $record) => $record->purchase_status !== 'purchased')
                        ->modalHeading(fn(AssetRequests $record) => 'Selesaikan Pembelian: ' . $record->document_number)
                        ->modalDescription(fn(AssetRequests $record) => "Akan membuat {$record->quantity} aset baru dengan nomor aset otomatis.")
                        ->modalSubmitActionLabel('Simpan & Buat Aset')
                        // ->modalWidth('4xl')
                        ->form(function (AssetRequests $record) {
                            return [
                                Forms\Components\Section::make('Info Permintaan')
                                    ->description('Data dari permintaan barang')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\Placeholder::make('asset_name_info')
                                                ->label('Nama Barang')
                                                ->content($record->asset_name),
                                            Forms\Components\Placeholder::make('category_info')
                                                ->label('Kategori')
                                                ->content($record->category?->name . ' (' . $record->category?->kode . ')'),
                                            Forms\Components\Placeholder::make('quantity_info')
                                                ->label('Jumlah')
                                                ->content($record->quantity . ' buah'),
                                        ]),
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\Placeholder::make('employee_info')
                                                ->label('Pemohon')
                                                ->content($record->employee?->name ?? '-'),
                                            Forms\Components\Placeholder::make('location_info')
                                                ->label('Lokasi')
                                                ->content($record->location?->name . ' (' . ($record->location?->kode ?? '-') . ')'),
                                            Forms\Components\Placeholder::make('sub_location_info')
                                                ->label('Sub Lokasi')
                                                ->content($record->subLocation?->name ?? '-'),
                                        ]),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

                                Forms\Components\Section::make('Data Pembelian & Aset Baru')
                                    ->description('Lengkapi data pembelian. Nomor aset akan di-generate otomatis.')
                                    ->icon('heroicon-o-shopping-cart')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\DatePicker::make('purchase_date')
                                                ->label('Tanggal Pembelian')
                                                ->required()
                                                ->default(now())
                                                ->maxDate(now())
                                                ->live()
                                                ->afterStateUpdated(function ($state, $set) use ($record) {
                                                    if ($state && $record->category_id && $record->location_id) {
                                                        $preview = AssetNumberGenerator::preview(
                                                            $record->category_id,
                                                            $record->location_id,
                                                            $state,
                                                            $record->quantity
                                                        );
                                                        $set('asset_numbers_preview', implode("\n", $preview));
                                                    }
                                                }),

                                            Forms\Components\TextInput::make('brand')
                                                ->label('Merk / Tipe')
                                                ->required()
                                                ->maxLength(255),
                                        ]),

                                        Forms\Components\Textarea::make('asset_numbers_preview')
                                            ->label('Preview Nomor Aset')
                                            ->rows($record->quantity > 5 ? 5 : $record->quantity)
                                            ->disabled()
                                            ->default(function () use ($record) {
                                                if ($record->category_id && $record->location_id) {
                                                    $preview = AssetNumberGenerator::preview(
                                                        $record->category_id,
                                                        $record->location_id,
                                                        now(),
                                                        $record->quantity
                                                    );
                                                    return implode("\n", $preview);
                                                }
                                                return 'Pastikan kategori dan lokasi sudah terisi di permintaan';
                                            })
                                            ->helperText('Nomor aset akan di-generate otomatis berdasarkan: Kode Kategori, Kode Lokasi, Urutan Tahun, Tanggal Pembelian')
                                            ->columnSpanFull(),

                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('price')
                                                ->label('Harga Satuan')
                                                ->required()
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->minValue(1)
                                                ->helperText('Harga per unit'),

                                            Forms\Components\TextInput::make('funding_source')
                                                ->label('Sumber Dana')
                                                ->required()
                                                ->maxLength(255),
                                        ]),

                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\Select::make('condition_id')
                                                ->label('Kondisi Aset')
                                                ->options(\App\Models\MasterAssetsCondition::pluck('name', 'id'))
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->default(function () {
                                                    return \App\Models\MasterAssetsCondition::where('name', 'like', '%baru%')->first()?->id;
                                                }),

                                            Forms\Components\Select::make('status_id')
                                                ->label('Status Aset')
                                                ->options(\App\Models\MasterAssetsStatus::pluck('name', 'id'))
                                                ->default(function () {
                                                    return \App\Models\MasterAssetsStatus::where('name', 'Aktif')
                                                        ->orWhere('name', 'Active')
                                                        ->first()?->id;
                                                })
                                                ->required()
                                                ->searchable()
                                                ->preload(),
                                        ]),

                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('book_value')
                                                ->label('Nilai Buku')
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->default(0)
                                                ->helperText('Kosongkan jika sama dengan harga beli'),

                                            Forms\Components\DatePicker::make('book_value_expiry')
                                                ->label('Habis Nilai Buku')
                                                ->default(now()->addYears(5)),
                                        ]),

                                        Forms\Components\FileUpload::make('img')
                                            ->label('Foto Aset')
                                            ->directory('assets')
                                            ->disk('public')
                                            ->image()
                                            ->imageEditor()
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                            ->helperText('Maks 5MB. JPG/PNG. Foto yang sama akan digunakan untuk semua aset.')
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('purchase_notes')
                                            ->label('Catatan Pembelian')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ];
                        })
                        ->action(function (AssetRequests $record, array $data) {
                            // Validasi: pastikan category dan location ada
                            if (!$record->category_id || !$record->location_id) {
                                Notification::make()
                                    ->danger()
                                    ->title('Error!')
                                    ->body('Permintaan harus memiliki Kategori dan Lokasi yang valid.')
                                    ->send();
                                return;
                            }

                            DB::transaction(function () use ($record, $data) {
                                $quantity = $record->quantity;
                                $bookValue = $data['book_value'] ?: $data['price'];

                                // PENTING: Generate sequential number SEKALI sebelum loop
                                // agar semua item dalam 1 permintaan mendapat nomor urut yang sama
                                $purchaseDate = new \DateTime($data['purchase_date']);
                                $sequentialNumber = AssetNumberGenerator::getYearlySequentialNumber($purchaseDate->format('Y'));

                                for ($i = 1; $i <= $quantity; $i++) {
                                    // Generate nomor aset dengan sequential number yang sudah dihitung
                                    $assetNumber = AssetNumberGenerator::generate(
                                        $record->category_id,
                                        $record->location_id,
                                        $data['purchase_date'],
                                        $i,
                                        $sequentialNumber, // Gunakan sequential number yang SAMA untuk semua item!
                                        $quantity
                                    );

                                    // 1. Simpan ke Asset Purchase
                                    \App\Models\AssetPurchase::create([
                                        'assetrequest_id' => $record->id,
                                        'document_number' => $record->document_number,
                                        'assets_number' => $assetNumber,
                                        'asset_name' => $record->asset_name,
                                        'category_id' => $record->category_id,
                                        'employee_id' => $record->employee_id,
                                        'location_id' => $record->location_id,
                                        'sub_location_id' => $record->sub_location_id,
                                        'brand' => $data['brand'],
                                        'purchase_date' => $data['purchase_date'],
                                        'condition_id' => $data['condition_id'],
                                        'status_id' => $data['status_id'],
                                        'price' => $data['price'],
                                        'book_value' => $bookValue,
                                        'book_value_expiry' => $data['book_value_expiry'],
                                        'funding_source' => $data['funding_source'],
                                        'img' => $data['img'] ?? null,
                                        'purchase_notes' => $data['purchase_notes'] ?? null,
                                        'item_index' => $i,
                                        'users_id' => auth()->id(),
                                    ]);

                                    // 2. Simpan ke Assets
                                    \App\Models\Asset::create([
                                        'assets_number' => $assetNumber,
                                        'name' => $record->asset_name,
                                        'category_id' => $record->category_id,
                                        'brand' => $data['brand'],
                                        'purchase_date' => $data['purchase_date'],
                                        'condition_id' => $data['condition_id'],
                                        'status_id' => $data['status_id'],
                                        'price' => $data['price'],
                                        'funding_source' => $data['funding_source'],
                                        'book_value' => $bookValue,
                                        'book_value_expiry' => $data['book_value_expiry'],
                                        'img' => $data['img'] ?? null,
                                        'desc' => $record->desc,
                                        'users_id' => auth()->id(),
                                    ]);
                                }

                                // 3. Update status permintaan
                                $record->update([
                                    'purchase_status' => 'purchased',
                                    'purchase_date' => $data['purchase_date'],
                                    'purchase_notes' => $data['purchase_notes'] ?? null,
                                ]);
                            });

                            Notification::make()
                                ->success()
                                ->title('Sukses!')
                                ->body("Pembelian selesai. {$record->quantity} aset baru telah ditambahkan.")
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-check-circle')
                        ->modalIconColor('success'),

                    // 2. Action Lain: View
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),

                    // 2b. Action: Cetak Faktur (hanya jika sudah dibeli)
                    Tables\Actions\Action::make('cetak_faktur')
                        ->label('Cetak Faktur')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->visible(fn(AssetRequests $record) => $record->purchase_status === 'purchased')
                        ->url(fn(AssetRequests $record) => route('purchase.invoice', ['record' => $record->id]))
                        ->openUrlInNewTab(),

                    Tables\Actions\EditAction::make()
                        ->label('Edit Permintaan')
                        ->visible(fn() => in_array(Auth::user()->role, ['super_admin', 'admin', 'kabag', 'kasubag'])),

                    // 4. Action Lain: Hapus (optional)
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation(),
                ])
                    ->label('Action')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('primary')
                    ->button() // jadi tombol bulat rapi
                    ->size('md'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada permintaan barang')
            ->emptyStateDescription('Data permintaan akan muncul setelah ada permintaan barang yang diinput.')
            ->emptyStateIcon('heroicon-o-shopping-cart');
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
            'index' => Pages\ListAssetPurchases::route('/'),
            'view' => Pages\ViewAssetPurchase::route('/{record}'),
        ];
    }
}
