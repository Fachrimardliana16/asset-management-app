<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetRequestsResource\Pages;
use App\Models\AssetRequests;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Layout;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn; // optional kalau mau

class AssetRequestsResource extends Resource
{
    protected static ?string $model = AssetRequests::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Asset';
    protected static ?string $navigationLabel = 'Permintaan Barang';
    protected static ?string $modelLabel = 'Permintaan Barang';
    protected static ?string $pluralModelLabel = 'Permintaan Barang';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Form Input Permintaan')
                    ->description('Input Data Permintaan Barang')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('document_number')
                                    ->label('Nomor Dokumen')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->default(fn() => 'PR-' . now()->format('Ym') . '-' . str_pad(AssetRequests::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count() + 1, 4, '0', STR_PAD_LEFT))
                                    ->disabledOn('edit') // edit tetap bisa lihat, tapi nggak bisa ubah
                                    ->dehydrated(), // tetap disimpan

                                Forms\Components\DatePicker::make('date')
                                    ->label('Tanggal Permintaan')
                                    ->required()
                                    ->default(now())
                                    ->validationMessages([
                                        'required' => 'Tanggal permintaan wajib diisi.',
                                    ]),

                                Forms\Components\TextInput::make('asset_name')
                                    ->label('Nama Barang')
                                    ->required()
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => 'Nama barang wajib diisi.',
                                        'max' => 'Nama barang maksimal 255 karakter.',
                                    ]),

                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->label('Kategori')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set) => $set('asset_name', null)),

                                Forms\Components\Select::make('employee_id')
                                    ->relationship('employee', 'name')
                                    ->label('Pegawai/Pemohon')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\Select::make('location_id')
                                    ->relationship('location', 'name')
                                    ->label('Lokasi')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(),

                                Forms\Components\Select::make('sub_location_id')
                                    ->relationship('subLocation', 'name')
                                    ->label('Sub Lokasi')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah Satuan')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->suffix(' buah')
                                    ->placeholder('Contoh: 5')
                                    ->helperText('Masukkan angka saja, tanpa kata "buah"'),

                                Forms\Components\TextInput::make('purpose')
                                    ->label('Untuk Keperluan')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Penggantian laptop rusak, renovasi ruang meeting'),
                            ]),

                        Forms\Components\Textarea::make('desc')
                            ->label('Keterangan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Pengesahan')
                    ->description('Pengesahan Permintaan')
                    ->columns(3)
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
                            ->content(fn($record) => $record?->user?->name ?? auth()->user()->name),

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
                // 1. Nomor Dokumen
                Tables\Columns\TextColumn::make('document_number')
                    ->label('No. Dokumen')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // 2. Tanggal Permintaan
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                // 3. Permintaan Barang (nama + jumlah + kategori dalam 1 kolom)
                Tables\Columns\TextColumn::make('asset_name')
                    ->label('Permintaan Barang')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->asset_name .
                            "\n" . $record->quantity . " buah" .
                            ($record->category?->name ? " · " . $record->category->name : "")
                    )
                    ->html()
                    ->wrap()
                    ->searchable()
                    ->limit(60),

                // Pegawai/Pemohon
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                // Lokasi
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                // 4. Keperluan + tooltip keterangan
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->limit(35)
                    ->tooltip(fn($record) => "Keperluan: " . $record->purpose . "\n\nKeterangan:\n" . ($record->desc ?: 'Tidak ada'))
                    ->searchable(),

                // 5. Status Pembelian + tanggal pembelian di bawah
                Tables\Columns\TextColumn::make('purchase_status')
                    ->label('Status Pembelian')
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
                    })
                    ->description(fn($record) => $record->purchase_date?->format('d M Y') ?: '−'),

                // 6. Pengesahan (Lengkap / -3 dll)
                Tables\Columns\TextColumn::make('kekurangan_pengesahan')
                    ->label('Pengesahan')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $fields = ['kepala_sub_bagian', 'kepala_bagian_umum', 'kepala_bagian_keuangan', 'direktur_umum', 'direktur_utama'];
                        $kurang = collect($fields)->filter(fn($f) => ! $record->{$f})->count();
                        return $kurang === 0 ? 'Lengkap' : "-{$kurang}";
                    })
                    ->color(fn($state) => $state === 'Lengkap' ? 'success' : 'danger')
                    ->icon(fn($state) => $state === 'Lengkap' ? 'heroicon-o-check-badge' : 'heroicon-o-clock')
                    ->alignCenter()
                    ->size('lg'),

                // 7. Lampiran (gambar kecil) + tanggal dibuat di title (hover)
                Tables\Columns\ImageColumn::make('docs')
                    ->label('Lampiran')
                    ->size(50)
                    ->rounded()
                    ->defaultImageUrl(asset('images/no-image.png')) // optional, buat placeholder
                    ->extraImgAttributes(fn($record) => [
                        'title' => 'Dibuat: ' . $record->created_at->format('d/m/Y H:i'),
                        'class' => 'border shadow-sm cursor-pointer'
                    ]),
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
            'edit'   => Pages\EditAssetRequests::route('/{record}/edit'),
        ];
    }
}
