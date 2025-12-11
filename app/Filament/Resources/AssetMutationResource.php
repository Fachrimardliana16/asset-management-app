<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetMutationResource\Pages;
use App\Filament\Resources\AssetMutationResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetMutation;
use App\Models\MasterAssetsTransactionStatus;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class AssetMutationResource extends Resource
{
    protected static ?string $model = AssetMutation::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Asset';
    protected static ?string $navigationLabel = 'Mutasi Barang';
    protected static ?string $modelLabel = 'Mutasi Barang';
    protected static ?string $pluralModelLabel = 'Mutasi Barang';
    protected static ?int $navigationSort = 6;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Form Mutasi Barang')
                    ->description('Mutasi Keluar: Gudang → Individu | Mutasi Masuk: Individu → Gudang')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('mutations_number')
                                ->label('Nomor Mutasi')
                                ->required()
                                ->placeholder('Contoh: MUT-001')
                                ->helperText('Nomor unik untuk identifikasi mutasi'),
                            Forms\Components\DatePicker::make('mutation_date')
                                ->label('Tanggal Mutasi')
                                ->required()
                                ->default(now()),
                        ]),
                        Forms\Components\Section::make('Jenis Mutasi')
                            ->description('Pilih jenis mutasi barang')
                            ->schema([
                                Forms\Components\Select::make('transaction_status_id')
                                    ->relationship('AssetsMutationtransactionStatus', 'name')
                                    ->label('Jenis Mutasi')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($set) {
                                        // Reset asset selection when transaction type changes
                                        $set('assets_id', null);
                                        $set('assets_number', null);
                                        $set('name', null);
                                        $set('condition_id', null);
                                    })
                                    ->helperText('Mutasi Keluar: Aset dari gudang ke individu/pegawai. Mutasi Masuk: Aset dari individu kembali ke gudang.'),
                            ])->collapsible(false)->columns(1),
                        Forms\Components\Section::make('Data Aset')
                            ->schema([
                                Forms\Components\Select::make('assets_id')
                                    ->label('Pilih Aset')
                                    ->options(function ($get) {
                                        $transactionStatusId = $get('transaction_status_id');

                                        // Get transaction status name
                                        $transactionStatus = MasterAssetsTransactionStatus::find($transactionStatusId);
                                        $isKeluarTransaction = $transactionStatus && $transactionStatus->name === 'Transaksi Keluar';

                                        // Count keluar and masuk for each asset
                                        $keluarCounts = AssetMutation::query()
                                            ->select('assets_id', DB::raw('COUNT(*) as count'))
                                            ->whereHas('AssetsMutationtransactionStatus', fn($q) => $q->where('name', 'Transaksi Keluar'))
                                            ->groupBy('assets_id')
                                            ->pluck('count', 'assets_id')
                                            ->toArray();

                                        $masukCounts = AssetMutation::query()
                                            ->select('assets_id', DB::raw('COUNT(*) as count'))
                                            ->whereHas('AssetsMutationtransactionStatus', fn($q) => $q->where('name', 'Transaksi Masuk'))
                                            ->groupBy('assets_id')
                                            ->pluck('count', 'assets_id')
                                            ->toArray();

                                        // Assets that are still "out" = keluar count > masuk count
                                        $assetsStillOut = [];
                                        foreach ($keluarCounts as $assetId => $keluarCount) {
                                            $masukCount = $masukCounts[$assetId] ?? 0;
                                            if ($keluarCount > $masukCount) {
                                                $assetsStillOut[] = $assetId;
                                            }
                                        }

                                        $query = Asset::query()
                                            ->where('status_id', function ($query) {
                                                $query->select('id')
                                                    ->from('master_assets_status')
                                                    ->where('name', 'Active')
                                                    ->limit(1);
                                            });

                                        if ($isKeluarTransaction) {
                                            // Mutasi Keluar: show assets NOT currently out (di gudang)
                                            $query->whereNotIn('id', $assetsStillOut);
                                        } else {
                                            // Mutasi Masuk: show assets currently out (di pegawai)
                                            $query->whereIn('id', $assetsStillOut);
                                        }

                                        return $query->pluck('assets_number', 'id');
                                    })
                                    ->afterStateUpdated(function ($set, $state) {
                                        $aset = Asset::find($state);
                                        if ($aset) {
                                            $set('assets_number', $aset->assets_number);
                                            $set('name', $aset->name);
                                            $set('condition_id', $aset->condition_id);
                                        } else {
                                            $set('assets_number', null);
                                            $set('name', null);
                                            $set('condition_id', null);
                                        }
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->helperText(fn($get) => $get('transaction_status_id')
                                        ? 'Pilih aset yang akan dimutasi'
                                        : 'Pilih jenis mutasi terlebih dahulu'),
                                Forms\Components\Hidden::make('assets_number')
                                    ->default(function ($get) {
                                        $assets_id = $get('assets_id');
                                        $asset = Asset::find($assets_id);
                                        return $asset ? $asset->assets_number : null;
                                    }),
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nama Aset')
                                        ->required()
                                        ->readonly()
                                        ->placeholder('Terisi otomatis setelah pilih aset'),
                                    Forms\Components\Select::make('condition_id')
                                        ->relationship('MutationCondition', 'name')
                                        ->label('Kondisi Aset')
                                        ->required()
                                        ->disabled()
                                        ->helperText('Kondisi saat ini'),
                                ]),
                                Forms\Components\Hidden::make('condition_id')
                                    ->default(function ($get) {
                                        return $get('condition_id');
                                    }),
                            ])->collapsible(),
                        Forms\Components\Section::make('Tujuan Mutasi')
                            ->description('Tentukan pemegang dan lokasi tujuan')
                            ->schema([
                                Forms\Components\Select::make('employees_id')
                                    ->relationship('AssetsMutationemployee', 'name')
                                    ->label('Pemegang/Penanggung Jawab')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pegawai yang akan bertanggung jawab atas aset'),
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('location_id')
                                        ->relationship('AssetsMutationlocation', 'name')
                                        ->label('Lokasi Tujuan')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Forms\Components\Select::make('sub_location_id')
                                        ->relationship('AssetsMutationsubLocation', 'name')
                                        ->label('Sub Lokasi')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                ]),
                            ])->collapsible(),
                        Forms\Components\Section::make('Dokumen & Keterangan')
                            ->schema([
                                Forms\Components\FileUpload::make('scan_doc')
                                    ->directory('mutation-assets')
                                    ->disk('public')
                                    ->label('Scan Dokumen/Berita Acara')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->helperText('Upload dokumen berita acara mutasi (PDF/Gambar)'),
                                Forms\Components\Textarea::make('desc')
                                    ->label('Keterangan/Catatan')
                                    ->placeholder('Tambahkan catatan atau keterangan mutasi...')
                                    ->columnSpanFull(),
                            ])->collapsible(),
                        Forms\Components\Hidden::make('users_id')
                            ->default(auth()->id()),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('No.')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('mutations_number')
                    ->label('No. Mutasi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mutation_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('AssetsMutationtransactionStatus.name')
                    ->label('Jenis Mutasi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Transaksi Keluar' => 'danger',
                        'Transaksi Masuk' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('AssetsMutation.assets_number')
                    ->label('No. Aset')
                    ->searchable(),
                Tables\Columns\TextColumn::make('AssetsMutation.name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('MutationCondition.name')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Rusak Ringan' => 'warning',
                        'Rusak Berat' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('AssetsMutationemployee.name')
                    ->label('Pemegang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('AssetsMutationlocation.name')
                    ->label('Lokasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('AssetsMutationsubLocation.name')
                    ->label('Sub Lokasi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('desc')
                    ->label('Keterangan')
                    ->wrap()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('transaction_status_id')
                    ->relationship('AssetsMutationtransactionStatus', 'name')
                    ->label('Jenis Mutasi'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),

                    Tables\Actions\Action::make('cetak_serah_terima')
                        ->label('Cetak Doc Serah Terima')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn($record) => route('assets.cetak-serah-terima', $record->id))
                        ->openUrlInNewTab()
                        ->tooltip('Cetak dokumen serah terima aset ini'),
                ])
                    ->label('Action')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('primary')
                    ->button()
                    ->size('md'),
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
            'index' => Pages\ListAssetMutations::route('/'),
            'create' => Pages\CreateAssetMutation::route('/create'),
            'edit' => Pages\EditAssetMutation::route('/{record}/edit'),
        ];
    }

    /**
     * Add eager loading to prevent N+1 query issues
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'AssetsMutation',
                'AssetsMutationtransactionStatus',
                'AssetsMutationemployee',
                'AssetsMutationlocation',
                'AssetsMutationsubLocation',
                'MutationCondition',
            ]);
    }
}
