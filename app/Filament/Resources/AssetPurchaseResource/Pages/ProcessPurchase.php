<?php

namespace App\Filament\Resources\AssetPurchaseResource\Pages;

use App\Filament\Resources\AssetPurchaseResource;
use App\Models\AssetRequests;
use App\Models\MasterTaxType;
use App\Services\AssetNumberGenerator;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ProcessPurchase extends Page
{
    protected static string $resource = AssetPurchaseResource::class;

    protected static string $view = 'filament.resources.asset-purchase-resource.pages.process-purchase';

    protected static ?string $title = 'Proses Pembelian Barang';

    public ?array $data = [];

    public AssetRequests $record;

    public function mount(AssetRequests $record): void
    {
        $this->record = $record;
        
        // Check if already purchased
        if ($record->purchase_status === 'purchased') {
            Notification::make()
                ->warning()
                ->title('Pembelian Sudah Diproses')
                ->body('Permintaan ini sudah diproses sebelumnya.')
                ->send();
            
            $this->redirect(AssetPurchaseResource::getUrl('index'));
            return;
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make($this->getWizardSteps())
                    ->submitAction(new HtmlString(\Illuminate\Support\Facades\Blade::render(<<<BLADE
                        <x-filament::button
                            type="submit"
                            size="lg"
                            icon="heroicon-o-check-circle"
                        >
                            Simpan Semua Pembelian
                        </x-filament::button>
                    BLADE)))
                    ->nextAction(
                        fn (\Filament\Forms\Components\Actions\Action $action) => $action->label('Lanjutkan')->icon('heroicon-m-arrow-right')->iconPosition('after')
                    )
                    ->previousAction(
                        fn (\Filament\Forms\Components\Actions\Action $action) => $action->label('Kembali')->icon('heroicon-m-arrow-left')
                    )
                    ->skippable(false)
                    ->persistStepInQueryString('step')
            ])
            ->statePath('data');
    }

    protected function getWizardSteps(): array
    {
        $record = $this->record;
        $items = $record->items;
        $steps = [];

        // Step 1: Informasi Permintaan (Read-only)
        $steps[] = Step::make('informasi')
            ->label('Informasi Permintaan')
            ->description('Ringkasan permintaan barang yang akan diproses')
            ->icon('heroicon-o-information-circle')
            ->completedIcon('heroicon-m-check-circle')
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Placeholder::make('document_info')
                                ->label('Nomor Dokumen')
                                ->content($record->document_number),

                            Forms\Components\Placeholder::make('date_info')
                                ->label('Tanggal Permintaan')
                                ->content(\Carbon\Carbon::parse($record->date)->format('d M Y')),

                            Forms\Components\Placeholder::make('department_info')
                                ->label('Department')
                                ->content($record->department?->name ?? '-'),

                            Forms\Components\Placeholder::make('requester_info')
                                ->label('Pemohon')
                                ->content($record->requestedBy ? $record->requestedBy->firstname . ' ' . $record->requestedBy->lastname : '-'),

                            Forms\Components\Placeholder::make('total_items_info')
                                ->label('Total Jenis Barang')
                                ->content($record->total_items . ' jenis'),

                            Forms\Components\Placeholder::make('total_qty_info')
                                ->label('Total Unit')
                                ->content($record->total_quantity . ' unit'),
                        ]),

                        Forms\Components\Placeholder::make('items_preview')
                            ->label('Daftar Barang')
                            ->content(new HtmlString(
                                '<div class="space-y-2 mt-2">' .
                                collect($record->items)->map(function($item, $index) {
                                    return '<div class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">' .
                                        '<span class="flex items-center justify-center w-8 h-8 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full font-semibold text-sm">' . ($index + 1) . '</span>' .
                                        '<div class="flex-1">' .
                                        '<span class="font-medium text-gray-900 dark:text-gray-100">' . $item->asset_name . '</span>' .
                                        '<span class="text-xs text-gray-500 dark:text-gray-400 ml-2">(' . ($item->category?->name ?? '-') . ')</span>' .
                                        '</div>' .
                                        '<span class="ml-auto text-sm font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/50 px-3 py-1 rounded-full">' . $item->quantity . ' unit</span>' .
                                        '</div>';
                                })->join('') .
                                '</div>'
                            ))
                            ->columnSpanFull(),
                    ])
            ]);

        // Step 2: Data Pembelian Global
        $steps[] = Step::make('data_global')
            ->label('Data Pembelian Global')
            ->description('Data ini akan diterapkan ke semua item')
            ->icon('heroicon-o-shopping-bag')
            ->completedIcon('heroicon-m-check-circle')
            ->columns(2)
            ->schema([
                Forms\Components\Section::make('Informasi Pembelian')
                    ->description('Lengkapi data pembelian yang berlaku untuk semua aset')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\DatePicker::make('purchase_date')
                                ->label('Tanggal Pembelian')
                                ->required()
                                ->default(now())
                                ->maxDate(now())
                                ->native(false),

                            Forms\Components\TextInput::make('funding_source')
                                ->label('Sumber Dana')
                                ->required()
                                ->maxLength(255)
                                ->default('RKA 2025')
                                ->placeholder('Contoh: RKA 2025'),

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
                                ->default(now()->addYears(5))
                                ->native(false),

                            Forms\Components\Textarea::make('purchase_notes')
                                ->label('Catatan Pembelian')
                                ->rows(3)
                                ->placeholder('Catatan tambahan tentang pembelian ini (opsional)')
                                ->columnSpanFull(),
                        ]),
                    ])
            ]);

        // Step 3+: Per Item (Dynamic Steps)
        foreach ($items as $index => $item) {
            $remaining = $item->remaining_quantity;
            if ($remaining <= 0) continue; // Skip jika sudah dibeli lengkap

            $steps[] = Step::make("item_{$item->id}")
                ->label("Item " . ($index + 1))
                ->description($item->asset_name . ' - ' . $item->quantity . ' unit')
                ->icon('heroicon-o-cube')
                ->completedIcon('heroicon-m-check-badge')
                ->schema([
                    Forms\Components\Section::make("Detail: {$item->asset_name}")
                        ->description("Kategori: {$item->category?->name} | Lokasi: {$item->location?->name} | Qty: {$item->quantity} unit")
                        ->schema([
                            Forms\Components\Hidden::make("items.{$item->id}.item_id")
                                ->default($item->id),

                            Forms\Components\Placeholder::make("items.{$item->id}.info")
                                ->label('📌 Penting')
                                ->content(new HtmlString(
                                    "<div class='text-sm bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 p-4 rounded-lg'>" .
                                    "<p class='font-semibold text-blue-900 dark:text-blue-100 mb-2'>Lengkapi data untuk setiap unit aset:</p>" .
                                    "<ul class='list-disc list-inside space-y-1 text-blue-800 dark:text-blue-200'>" .
                                    "<li>Setiap unit akan mendapat <strong>nomor aset unik</strong></li>" .
                                    "<li>Upload <strong>foto berbeda</strong> untuk setiap unit</li>" .
                                    "<li>Total unit yang harus diinput: <strong>{$item->quantity} unit</strong></li>" .
                                    "</ul>" .
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
                                            ->minValue(1)
                                            ->placeholder('0'),

                                        Forms\Components\TextInput::make('book_value')
                                            ->label('Nilai Buku')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->placeholder('Kosongkan jika sama dengan harga')
                                            ->helperText('Opsional - default sama dengan harga'),
                                    ]),

                                    Forms\Components\FileUpload::make('img')
                                        ->label('Foto Aset')
                                        ->directory('assets')
                                        ->disk('public')
                                        ->image()
                                        ->imageEditor()
                                        ->imagePreviewHeight('250')
                                        ->maxSize(5120)
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                        ->helperText('Wajib. Upload foto untuk unit ini. Maksimal 5MB.')
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

                            // Section untuk input pajak (opsional)
                            Forms\Components\Section::make('Data Pajak')
                                ->description('Opsional - Catat pajak aset jika ada')
                                ->icon('heroicon-o-document-text')
                                ->collapsible()
                                ->collapsed(true)
                                ->schema([
                                    Forms\Components\Toggle::make("items.{$item->id}.has_taxes")
                                        ->label('Catat pajak sekarang?')
                                        ->helperText('Aktifkan jika ingin mencatat data pajak untuk aset ini')
                                        ->live()
                                        ->default(false)
                                        ->columnSpanFull(),

                                    Forms\Components\Repeater::make("items.{$item->id}.taxes")
                                        ->label('Daftar Pajak')
                                        ->schema([
                                            Forms\Components\Select::make('tax_type_id')
                                                ->label('Jenis Pajak')
                                                ->options(function () use ($item) {
                                                    // Filter tax types by category
                                                    return MasterTaxType::where('asset_category_id', $item->category_id)
                                                        ->where('is_active', true)
                                                        ->pluck('name', 'id');
                                                })
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->helperText('Pilih jenis pajak sesuai kategori aset'),

                                            Forms\Components\TextInput::make('tax_amount')
                                                ->label('Nilai Pajak')
                                                ->required()
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->minValue(1)
                                                ->placeholder('0'),

                                            Forms\Components\DatePicker::make('due_date')
                                                ->label('Tanggal Jatuh Tempo')
                                                ->required()
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->minDate(now())
                                                ->helperText('Tanggal jatuh tempo pembayaran pajak'),

                                            Forms\Components\Textarea::make('notes')
                                                ->label('Catatan')
                                                ->maxLength(500)
                                                ->rows(2)
                                                ->placeholder('Catatan tambahan tentang pajak ini...')
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(3)
                                        ->itemLabel(fn (array $state): ?string => 
                                            isset($state['tax_type_id']) 
                                                ? MasterTaxType::find($state['tax_type_id'])?->name 
                                                : 'Pajak Baru'
                                        )
                                        ->collapsible()
                                        ->collapsed(false)
                                        ->addActionLabel('+ Tambah Pajak Lain')
                                        ->visible(fn (Forms\Get $get) => $get("items.{$item->id}.has_taxes") === true)
                                        ->columnSpanFull()
                                        ->helperText('Anda dapat menambahkan lebih dari satu jenis pajak'),
                                ])
                                ->visible(function () use ($item) {
                                    // Only show if category has tax types
                                    return MasterTaxType::where('asset_category_id', $item->category_id)
                                        ->where('is_active', true)
                                        ->exists();
                                })
                                ->columnSpanFull(),

                            Forms\Components\Placeholder::make("items.{$item->id}.preview")
                                ->label('📋 Preview Nomor Aset')
                                ->content(function () use ($item) {
                                    $preview = AssetNumberGenerator::preview(
                                        $item->category_id,
                                        $item->location_id,
                                        now(),
                                        $item->quantity
                                    );
                                    return new HtmlString(
                                        '<div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">' .
                                        '<p class="text-xs text-gray-600 dark:text-gray-400 mb-2 font-medium">Nomor aset yang akan dibuat:</p>' .
                                        '<code class="text-sm text-gray-800 dark:text-gray-200 font-mono leading-relaxed">' . implode('<br>', $preview) . '</code>' .
                                        '</div>'
                                    );
                                })
                                ->columnSpanFull(),
                        ])
                ]);
        }

        // Step Terakhir: Review & Konfirmasi
        $steps[] = Step::make('review')
            ->label('Review & Konfirmasi')
            ->description('Periksa kembali sebelum menyimpan')
            ->icon('heroicon-o-check-circle')
            ->completedIcon('heroicon-m-hand-thumb-up')
            ->schema([
                Forms\Components\Section::make('Konfirmasi Data')
                    ->description('Pastikan semua data sudah benar sebelum menyimpan')
                    ->schema([
                        Forms\Components\Placeholder::make('review_summary')
                            ->label('Ringkasan Pembelian')
                            ->content(function () use ($record) {
                                $totalAssets = $record->items->sum('quantity');
                                return new HtmlString(
                                    '<div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 p-6 rounded-lg">' .
                                    '<div class="flex items-center gap-3 mb-4">' .
                                    '<svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' .
                                    '<p class="font-bold text-green-900 dark:text-green-100 text-xl">Data Siap Disimpan</p>' .
                                    '</div>' .
                                    '<div class="space-y-3 text-sm text-green-800 dark:text-green-200 mb-4">' .
                                    '<div class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400">•</span><span>Dokumen: <strong>' . $record->document_number . '</strong></span></div>' .
                                    '<div class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400">•</span><span>Total jenis barang: <strong>' . $record->items->count() . '</strong></span></div>' .
                                    '<div class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400">•</span><span>Total unit aset yang akan dibuat: <strong>' . $totalAssets . ' aset</strong></span></div>' .
                                    '<div class="flex items-start gap-2"><span class="text-green-600 dark:text-green-400">•</span><span>Setiap aset akan mendapat nomor unik dan tersimpan ke database</span></div>' .
                                    '</div>' .
                                    '<div class="mt-4 pt-4 border-t border-green-300 dark:border-green-700">' .
                                    '<p class="text-xs text-green-700 dark:text-green-300 font-medium">⚠️ Klik tombol <strong>"Simpan Semua Pembelian"</strong> di bawah untuk memproses pembelian ini.</p>' .
                                    '</div>' .
                                    '</div>'
                                );
                            })
                            ->columnSpanFull(),
                    ])
            ]);

        return $steps;
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $record = $this->record;

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
                        'img' => $img,
                        'purchase_notes' => $purchaseNotes,
                        'item_index' => $i,
                        'users_id' => auth()->id(),
                    ]);

                    // 2. Simpan ke Assets
                    $createdAsset = \App\Models\Asset::create([
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
                        'img' => $img,
                        'desc' => $requestItem->notes ?? $record->desc,
                        'users_id' => auth()->id(),
                    ]);

                    // 3. Simpan pajak jika ada (hanya untuk unit pertama per item)
                    if ($i === 1 && isset($itemData['has_taxes']) && $itemData['has_taxes'] === true) {
                        $taxes = $itemData['taxes'] ?? [];
                        foreach ($taxes as $taxData) {
                            $dueDate = new \DateTime($taxData['due_date']);
                            \App\Models\AssetTax::create([
                                'asset_id' => $createdAsset->id,
                                'tax_type_id' => $taxData['tax_type_id'],
                                'tax_year' => $dueDate->format('Y'),
                                'tax_amount' => $taxData['tax_amount'],
                                'due_date' => $taxData['due_date'],
                                'payment_status' => 'pending',
                                'approval_status' => 'pending',
                                'penalty_amount' => 0,
                                'notes' => $taxData['notes'] ?? null,
                            ]);
                        }
                    }

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

        $this->redirect(AssetPurchaseResource::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(AssetPurchaseResource::getUrl('index')),
        ];
    }
}
