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
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Permintaan')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_items')
                            ->label('Total Jenis Barang')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_quantity')
                            ->label('Total Unit')
                            ->disabled(),
                        Forms\Components\Textarea::make('desc')
                            ->label('Keterangan')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['items', 'purchases', 'department']))
            ->columns([
                // 1. No. DBP & Tanggal
                TextColumn::make('document_number')
                    ->label('Info DPB')
                    ->html()
                    ->formatStateUsing(fn($record) => new HtmlString(
                        "<div class='font-medium'>{$record->document_number}</div>" .
                            "<div class='mt-1 text-sm text-gray-600'>{$record->date->format('d M Y')}</div>"
                    ))
                    ->searchable()
                    ->sortable(),

                // 2. Items Summary (Multiple Items)
                TextColumn::make('items_summary')
                    ->label('Detail Barang')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        $items = $record->items;
                        if ($items->isEmpty()) {
                            return new HtmlString('<span class="text-gray-400 italic">Belum ada item</span>');
                        }

                        $summary = $items->take(2)->map(function ($item) {
                            return "<div class='mb-1'>" .
                                "<span class='font-medium'>{$item->asset_name}</span> " .
                                "<span class='text-xs'>({$item->category?->name})</span> " .
                                "<span class='text-xs text-gray-500'>{$item->quantity} unit</span>" .
                                "</div>";
                        })->join('');

                        if ($items->count() > 2) {
                            $more = $items->count() - 2;
                            $summary .= "<div class='text-xs text-blue-600'>+{$more} item lainnya</div>";
                        }

                        return new HtmlString($summary);
                    }),

                // 3. Department & Pemohon
                TextColumn::make('department.name')
                    ->label('Departemen')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('requestedBy')
                    ->label('Pemohon')
                    ->formatStateUsing(fn($record) => $record->requestedBy ? ($record->requestedBy->firstname . ' ' . $record->requestedBy->lastname) : '-')
                    ->sortable()
                    ->toggleable(),

                // 4. Total Items & Quantity
                Tables\Columns\TextColumn::make('total_items')
                    ->label('Total')
                    ->html()
                    ->formatStateUsing(fn($record) => new HtmlString(
                        "<div class='font-medium'>{$record->total_items} jenis</div>" .
                            "<div class='text-sm text-gray-600'>{$record->total_quantity} unit</div>"
                    ))
                    ->alignCenter()
                    ->sortable(),

                // 5. Progress Pembelian
                TextColumn::make('purchase_progress')
                    ->label('Progress')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        $totalQty = $record->total_quantity;
                        $purchasedQty = $record->purchases()->count();
                        $percentage = $totalQty > 0 ? min(100, ($purchasedQty / $totalQty) * 100) : 0;
                        
                        $color = $percentage === 0 ? 'gray' : ($percentage === 100 ? 'green' : 'blue');
                        
                        return new HtmlString(
                            "<div class='flex items-center gap-2'>" .
                                "<div class='flex-1 bg-gray-200 rounded-full h-2'>" .
                                    "<div class='bg-{$color}-600 h-2 rounded-full' style='width: {$percentage}%'></div>" .
                                "</div>" .
                                "<span class='text-xs font-medium'>{$purchasedQty}/{$totalQty}</span>" .
                            "</div>"
                        );
                    }),

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
                Tables\Filters\SelectFilter::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Departemen')
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

                    // 1. Action Utama: Proses Pembelian Per Item
                    Tables\Actions\Action::make('proses_pembelian')
                        ->label('Proses Pembelian')
                        ->icon('heroicon-o-shopping-cart')
                        ->color('success')
                        ->visible(fn(AssetRequests $record) => $record->purchase_status !== 'purchased' && $record->items()->count() > 0)
                        ->modalHeading(fn(AssetRequests $record) => 'Proses Pembelian: ' . $record->document_number)
                        ->modalDescription('Lengkapi pembelian untuk setiap item yang diminta')
                        ->modalSubmitActionLabel('Simpan Semua Pembelian')
                        ->modalWidth('7xl')
                        ->form(function (AssetRequests $record) {
                            $items = $record->items;
                            $sections = [];

                            // Info Permintaan
                            $sections[] = Forms\Components\Section::make('Informasi Permintaan')
                                ->description("DBP: {$record->document_number} | Tanggal: {$record->date->format('d M Y')}")
                                ->schema([
                                    Forms\Components\Placeholder::make('summary')
                                        ->label('Ringkasan')
                                        ->content(new HtmlString(
                                            "<div class='space-y-1'>" .
                                            "<div><strong>Total Items:</strong> {$record->total_items} jenis barang</div>" .
                                            "<div><strong>Total Quantity:</strong> {$record->total_quantity} unit</div>" .
                                            "<div><strong>Department:</strong> " . ($record->department?->name ?? '-') . "</div>" .
                                            "<div><strong>Pemohon:</strong> " . ($record->requestedBy ? $record->requestedBy->firstname . ' ' . $record->requestedBy->lastname : '-') . "</div>" .
                                            "</div>"
                                        )),
                                ])
                                ->collapsible()
                                ->collapsed();

                            // Data Pembelian Global (berlaku untuk semua item)
                            $sections[] = Forms\Components\Section::make('Data Pembelian Global')
                                ->description('Data ini akan diterapkan ke semua item')
                                ->schema([
                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\DatePicker::make('purchase_date')
                                            ->label('Tanggal Pembelian')
                                            ->required()
                                            ->default(now())
                                            ->maxDate(now()),

                                        Forms\Components\TextInput::make('funding_source')
                                            ->label('Sumber Dana')
                                            ->required()
                                            ->maxLength(255)
                                            ->default('RKA 2025'),

                                        Forms\Components\Select::make('condition_id')
                                            ->label('Kondisi Aset')
                                            ->options(\App\Models\MasterAssetsCondition::pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->default(function () {
                                                return \App\Models\MasterAssetsCondition::where('name', 'like', '%baru%')->first()?->id;
                                            }),
                                    ]),
                                    Forms\Components\Grid::make(3)->schema([
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

                                        Forms\Components\DatePicker::make('book_value_expiry')
                                            ->label('Habis Nilai Buku')
                                            ->default(now()->addYears(5)),

                                        Forms\Components\Textarea::make('purchase_notes')
                                            ->label('Catatan Pembelian')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ]),
                                ])
                                ->collapsible();

                            // Section untuk setiap item
                            foreach ($items as $index => $item) {
                                $remaining = $item->remaining_quantity;
                                if ($remaining <= 0) continue; // Skip jika sudah dibeli lengkap

                                $sections[] = Forms\Components\Section::make("Item #" . ($index + 1) . ": {$item->asset_name}")
                                    ->description("Kategori: {$item->category?->name} | Lokasi: {$item->location?->name} | Diminta: {$item->quantity} unit | Sisa: {$remaining} unit")
                                    ->schema([
                                        Forms\Components\Hidden::make("items.{$item->id}.item_id")
                                            ->default($item->id),

                                        Forms\Components\Placeholder::make("items.{$item->id}.info")
                                            ->label('Informasi')
                                            ->content(new HtmlString(
                                                "<div class='text-sm text-gray-700 bg-blue-50 p-3 rounded-md'>" .
                                                "<strong>⚠️ Perhatian:</strong> Isi data untuk <strong>setiap unit aset</strong> yang dibeli. " .
                                                "Setiap unit akan mendapat nomor aset dan foto yang berbeda." .
                                                "</div>"
                                            ))
                                            ->columnSpanFull(),

                                        // Repeater untuk setiap unit barang
                                        Forms\Components\Repeater::make("items.{$item->id}.units")
                                            ->label('Detail Per Unit Aset')
                                            ->schema([
                                                Forms\Components\Grid::make(3)->schema([
                                                    Forms\Components\TextInput::make('brand')
                                                        ->label('Merk / Tipe')
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->placeholder('Contoh: Dell Latitude 5420'),

                                                    Forms\Components\TextInput::make('price')
                                                        ->label('Harga Satuan')
                                                        ->required()
                                                        ->numeric()
                                                        ->prefix('Rp')
                                                        ->minValue(1),

                                                    Forms\Components\TextInput::make('book_value')
                                                        ->label('Nilai Buku')
                                                        ->numeric()
                                                        ->prefix('Rp')
                                                        ->default(0)
                                                        ->helperText('Kosongkan atau isi 0 jika sama dengan harga'),
                                                ]),

                                                Forms\Components\FileUpload::make('img')
                                                    ->label('Foto Aset (untuk unit ini)')
                                                    ->directory('assets')
                                                    ->disk('public')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->maxSize(5120)
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                                    ->helperText('Foto WAJIB untuk setiap unit aset. Maks 5MB.')
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ])
                                            ->defaultItems($item->quantity)
                                            ->minItems($item->quantity)
                                            ->maxItems($item->quantity)
                                            ->itemLabel(fn ($state): ?string => isset($state['brand']) ? "Unit: {$state['brand']}" : null)
                                            ->collapsible()
                                            ->collapsed(false)
                                            ->addable(false)
                                            ->deletable(false)
                                            ->reorderable(false)
                                            ->columnSpanFull(),

                                        Forms\Components\Placeholder::make("items.{$item->id}.preview")
                                            ->label('Preview Nomor Aset yang Akan Dibuat')
                                            ->content(function () use ($item) {
                                                $preview = AssetNumberGenerator::preview(
                                                    $item->category_id,
                                                    $item->location_id,
                                                    now(),
                                                    $item->quantity
                                                );
                                                return new HtmlString('<code class="text-xs">' . implode('<br>', $preview) . '</code>');
                                            })
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->icon('heroicon-o-cube');
                            }

                            return $sections;
                        })
                        ->action(function (AssetRequests $record, array $data) {
                            DB::transaction(function () use ($record, $data) {
                                $purchaseDate = $data['purchase_date'];
                                $purchaseDateObj = new \DateTime($purchaseDate);
                                $conditionId = $data['condition_id'];
                                $statusId = $data['status_id'];
                                $fundingSource = $data['funding_source'];
                                $bookValueExpiry = $data['book_value_expiry'];
                                $purchaseNotes = $data['purchase_notes'] ?? null;
                                
                                $totalCreated = 0;

                                // Generate sequential number SATU KALI untuk seluruh request ini
                                $sequentialNumber = AssetNumberGenerator::getYearlySequentialNumber($purchaseDateObj->format('Y'));

                                // Loop setiap item yang diinput
                                foreach ($data['items'] ?? [] as $itemId => $itemData) {
                                    // Cari item dari DB
                                    $requestItem = $record->items()->find($itemId);
                                    if (!$requestItem) continue;

                                    // Ambil data units (array per unit)
                                    $units = $itemData['units'] ?? [];
                                    if (empty($units)) continue;

                                    // Buat aset untuk setiap unit
                                    foreach ($units as $unitIndex => $unitData) {
                                        $i = $unitIndex + 1; // 1-based index

                                        // Data dari form per unit
                                        $brand = $unitData['brand'] ?? null;
                                        $price = $unitData['price'] ?? 0;
                                        $bookValue = ($unitData['book_value'] ?? 0) ?: $price;
                                        $img = $unitData['img'] ?? null; // Foto per unit!

                                        // Generate nomor aset
                                        $assetNumber = AssetNumberGenerator::generate(
                                            $requestItem->category_id,
                                            $requestItem->location_id,
                                            $purchaseDate,
                                            $i,
                                            $sequentialNumber,
                                            count($units)
                                        );

                                        // 1. Simpan ke Asset Purchase
                                        \App\Models\AssetPurchase::create([
                                            'assetrequest_id' => $record->id,
                                            'asset_request_item_id' => $requestItem->id,
                                            'document_number' => $record->document_number,
                                            'assets_number' => $assetNumber,
                                            'asset_name' => $requestItem->asset_name,
                                            'category_id' => $requestItem->category_id,
                                            'employee_id' => null,
                                            'location_id' => $requestItem->location_id,
                                            'sub_location_id' => $requestItem->sub_location_id,
                                            'brand' => $brand,
                                            'purchase_date' => $purchaseDate,
                                            'condition_id' => $conditionId,
                                            'status_id' => $statusId,
                                            'price' => $price,
                                            'book_value' => $bookValue,
                                            'book_value_expiry' => $bookValueExpiry,
                                            'funding_source' => $fundingSource,
                                            'img' => $img, // Foto BERBEDA per unit
                                            'purchase_notes' => $purchaseNotes,
                                            'item_index' => $i,
                                            'users_id' => auth()->id(),
                                        ]);

                                        // 2. Simpan ke Assets
                                        \App\Models\Asset::create([
                                            'assets_number' => $assetNumber,
                                            'name' => $requestItem->asset_name,
                                            'category_id' => $requestItem->category_id,
                                            'brand' => $brand,
                                            'purchase_date' => $purchaseDate,
                                            'condition_id' => $conditionId,
                                            'status_id' => $statusId,
                                            'price' => $price,
                                            'funding_source' => $fundingSource,
                                            'book_value' => $bookValue,
                                            'book_value_expiry' => $bookValueExpiry,
                                            'img' => $img, // Foto BERBEDA per unit
                                            'desc' => $requestItem->notes ?? $record->desc,
                                            'users_id' => auth()->id(),
                                        ]);

                                        $totalCreated++;
                                    }
                                }

                                // 3. Update status permintaan jika semua item sudah dibeli
                                if ($record->isAllItemsPurchased()) {
                                    $record->update([
                                        'purchase_status' => 'purchased',
                                        'purchase_date' => $purchaseDate,
                                        'purchase_notes' => $purchaseNotes,
                                    ]);
                                } else {
                                    $record->update([
                                        'purchase_status' => 'in_progress',
                                        'purchase_date' => $purchaseDate,
                                    ]);
                                }
                            });

                            Notification::make()
                                ->success()
                                ->title('Pembelian Berhasil!')
                                ->body("Berhasil membuat aset untuk permintaan ini.")
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
