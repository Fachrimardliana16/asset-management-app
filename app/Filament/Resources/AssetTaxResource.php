<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetTaxResource\Pages;
use App\Models\AssetTax;
use App\Models\Asset;
use App\Models\MasterTaxType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AssetTaxResource extends Resource
{
    protected static ?string $model = AssetTax::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Pajak Aset';

    protected static ?string $modelLabel = 'Pajak Aset';

    protected static ?string $pluralModelLabel = 'Pajak Aset';

    protected static ?string $navigationGroup = 'Asset';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pajak')
                    ->schema([
                        Forms\Components\Select::make('asset_id')
                            ->label('Aset')
                            ->relationship('asset', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $asset = Asset::find($state);
                                    if ($asset && $asset->category_id) {
                                        $set('filtered_category', $asset->category_id);
                                    }
                                }
                            })
                            ->helperText('Pilih aset yang akan dibayar pajaknya'),

                        Forms\Components\Select::make('tax_type_id')
                            ->label('Jenis Pajak')
                            ->options(function (Forms\Get $get) {
                                $categoryId = $get('filtered_category');
                                if ($categoryId) {
                                    return MasterTaxType::active()
                                        ->where('asset_category_id', $categoryId)
                                        ->pluck('name', 'id');
                                }
                                return MasterTaxType::active()->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state) {
                                    $taxType = MasterTaxType::find($state);
                                    if ($taxType) {
                                        // Set due date berdasarkan periode
                                        $currentYear = $get('tax_year') ?? now()->year;
                                        $dueDate = now()->setYear($currentYear)->addMonths($taxType->period_in_months);
                                        $set('due_date', $dueDate->format('Y-m-d'));
                                    }
                                }
                            })
                            ->helperText('Jenis pajak sesuai dengan kategori aset'),

                        Forms\Components\Hidden::make('filtered_category'),

                        Forms\Components\TextInput::make('tax_year')
                            ->label('Tahun Pajak')
                            ->numeric()
                            ->default(now()->year)
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('tax_amount')
                            ->label('Nilai Pajak')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->minValue(0)
                            ->step(1000)
                            ->helperText('Nominal pajak yang harus dibayar'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tanggal & Status')
                    ->schema([
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Tanggal Jatuh Tempo')
                            ->required()
                            ->minDate(now())
                            ->helperText('Batas waktu pembayaran pajak'),

                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->maxDate(now())
                            ->helperText('Tanggal saat pajak dibayar'),

                        Forms\Components\Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'pending' => 'Menunggu Pembayaran',
                                'paid' => 'Sudah Dibayar',
                                'overdue' => 'Terlambat',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Denda & Keterlambatan')
                    ->schema([
                        Forms\Components\Placeholder::make('calculated_penalty')
                            ->label('Kalkulasi Denda')
                            ->content(function ($record) {
                                if (!$record) return 'Simpan data untuk melihat kalkulasi';

                                $penalty = $record->calculatePenalty();
                                return $penalty['calculation'];
                            }),

                        Forms\Components\TextInput::make('penalty_amount')
                            ->label('Jumlah Denda')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Denda akan dihitung otomatis'),

                        Forms\Components\TextInput::make('overdue_days')
                            ->label('Hari Keterlambatan')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->suffix(' hari')
                            ->helperText('Jumlah hari terlambat dari jatuh tempo'),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record !== null),

                Forms\Components\Section::make('Bukti Pembayaran')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('payment_proofs')
                            ->label('Upload Bukti')
                            ->collection('payment_proofs')
                            ->multiple()
                            ->maxFiles(5)
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120)
                            ->helperText('Upload bukti pembayaran (Max 5MB per file, format: JPG, PNG, PDF)'),
                    ]),

                Forms\Components\Section::make('Catatan')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset.name')
                    ->label('Aset')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('asset.asset_code')
                    ->label('Kode Aset')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('taxType.name')
                    ->label('Jenis Pajak')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('tax_year')
                    ->label('Tahun')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('tax_amount')
                    ->label('Nilai Pajak')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('penalty_amount')
                    ->label('Denda')
                    ->money('IDR')
                    ->sortable()
                    ->color('danger')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn($record) => ($record && $record->isOverdue()) ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tgl Bayar')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('-'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Status Bayar')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger' => 'overdue',
                        'secondary' => 'cancelled',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'paid' => 'Lunas',
                        'pending' => 'Pending',
                        'overdue' => 'Terlambat',
                        'cancelled' => 'Batal',
                        default => $state,
                    }),


                Tables\Columns\TextColumn::make('overdue_days')
                    ->label('Hari Terlambat')
                    ->suffix(' hari')
                    ->alignCenter()
                    ->toggleable()
                    ->color('danger')
                    ->visible(fn($record) => $record && $record->overdue_days > 0),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('asset_id')
                    ->label('Aset')
                    ->relationship('asset', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('tax_type_id')
                    ->label('Jenis Pajak')
                    ->relationship('taxType', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('tax_year')
                    ->label('Tahun')
                    ->options(function () {
                        $years = [];
                        for ($i = now()->year; $i >= now()->year - 10; $i--) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Menunggu Pembayaran',
                        'paid' => 'Sudah Dibayar',
                        'overdue' => 'Terlambat',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->multiple(),


                Tables\Filters\Filter::make('overdue')
                    ->label('Terlambat')
                    ->query(fn(Builder $query) => $query->overdue()),

                Tables\Filters\Filter::make('upcoming')
                    ->label('Akan Jatuh Tempo (30 hari)')
                    ->query(fn(Builder $query) => $query->upcoming(30)),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Hanya action View - resource ini untuk histori saja
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),

                    // Hapus EditAction - tidak boleh edit histori
                    // Tables\Actions\EditAction::make(),

                    // Action Bayar tetap ada untuk update status pembayaran
                    Tables\Actions\Action::make('pay')
                        ->label('Bayar')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->form([
                            Forms\Components\DatePicker::make('payment_date')
                                ->label('Tanggal Pembayaran')
                                ->required()
                                ->default(now())
                                ->maxDate(now())
                                ->native(false),
                            Forms\Components\FileUpload::make('payment_proof')
                                ->label('Bukti Pembayaran')
                                ->image()
                                ->maxSize(5120)
                                ->directory('tax-payments')
                                ->helperText('Upload foto/scan bukti pembayaran (max 5MB)'),
                            Forms\Components\Textarea::make('payment_notes')
                                ->label('Catatan Pembayaran')
                                ->rows(3)
                                ->placeholder('Tambahkan catatan jika diperlukan...'),
                        ])
                        ->visible(
                            fn($record) =>
                            $record && $record->payment_status !== 'paid'
                        )
                        ->action(function ($record, array $data) {
                            $record->update([
                                'payment_status' => 'paid',
                                'payment_date' => $data['payment_date'],
                                'paid_by' => Auth::id(),
                                'notes' => ($record->notes ? $record->notes . "\n\n" : '') .
                                    "Pembayaran: " . ($data['payment_notes'] ?? ''),
                            ]);

                            // Save payment proof if uploaded
                            if (isset($data['payment_proof'])) {
                                $record->addMedia(storage_path('app/public/' . $data['payment_proof']))
                                    ->toMediaCollection('payment_proofs');
                            }

                            Notification::make()
                                ->title('Pembayaran Berhasil')
                                ->body('Pajak telah ditandai sebagai lunas')
                                ->success()
                                ->send();
                        }),

                    // Approval dihapus - auto approve
                    // Tables\Actions\Action::make('reject')

                    // Hapus action Update Penalty - sudah otomatis
                    // Tables\Actions\Action::make('update_penalty')

                    // Hapus DeleteAction - resource ini hanya read-only histori
                    // Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                // Hapus semua bulk actions - resource ini read-only
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('due_date', 'desc');
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
            'index' => Pages\ListAssetTaxes::route('/'),
            // Hapus Create dan Edit - resource ini read-only
            // 'create' => Pages\CreateAssetTax::route('/create'),
            // 'edit' => Pages\EditAssetTax::route('/{record}/edit'),
            'view' => Pages\ViewAssetTax::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('payment_status', 'overdue')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('payment_status', 'overdue')->count() > 0 ? 'danger' : 'primary';
    }
}
