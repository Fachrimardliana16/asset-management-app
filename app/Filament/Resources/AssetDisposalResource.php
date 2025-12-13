<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetDisposalResource\Pages;
use App\Filament\Resources\AssetDisposalResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetDisposal;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssetDisposalResource extends Resource
{
    protected static ?string $model = AssetDisposal::class;

    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static ?string $navigationGroup = 'Asset';
    protected static ?string $navigationLabel = 'Penghapusan Barang';
    protected static ?string $modelLabel = 'Penghapusan Barang';
    protected static ?string $pluralModelLabel = 'Penghapusan Barang';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Form Penghapusan Barang')
                    ->description('Penghapusan barang akan mengubah status aset menjadi Inactive. Data aset tetap tersimpan sebagai arsip.')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('disposals_number')
                                ->label('Nomor Penghapusan')
                                ->required()
                                ->placeholder('Contoh: DSP-001')
                                ->helperText('Nomor unik untuk identifikasi penghapusan'),
                            Forms\Components\DatePicker::make('disposal_date')
                                ->label('Tanggal Penghapusan')
                                ->required()
                                ->default(now()),
                        ]),
                        Forms\Components\Section::make('Data Aset yang Akan Dihapus')
                            ->schema([
                                Forms\Components\Select::make('assets_id')
                                    ->label('Pilih Aset')
                                    ->options(
                                        Asset::query()
                                            ->whereHas('assetsStatus', fn($q) => $q->where('name', 'Active'))
                                            ->get()
                                            ->mapWithKeys(fn($asset) => [$asset->id => $asset->assets_number . ' - ' . $asset->name])
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Hanya menampilkan aset dengan status Active. Setelah penghapusan, status akan berubah menjadi Inactive.'),
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('book_value')
                                        ->label('Nilai Buku')
                                        ->required()
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->helperText('Nilai buku aset saat ini'),
                                    Forms\Components\TextInput::make('disposal_value')
                                        ->label('Nilai Penghapusan/Penjualan')
                                        ->required()
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->default(0)
                                        ->helperText('Nilai jika dijual, atau 0 jika dihapus'),
                                ]),
                            ])->collapsible(),
                        Forms\Components\Section::make('Alasan & Proses Penghapusan')
                            ->schema([
                                Forms\Components\Textarea::make('disposal_reason')
                                    ->label('Alasan Penghapusan')
                                    ->required()
                                    ->rows(3)
                                    ->placeholder('Jelaskan alasan mengapa aset perlu dihapus...')
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('disposal_process')
                                    ->label('Proses Penghapusan')
                                    ->options([
                                        'dimusnahkan' => 'Dimusnahkan',
                                        'dijual' => 'Dijual',
                                        'dihibahkan' => 'Dihibahkan',
                                        'dihapus dari inventaris' => 'Dihapus dari Daftar Inventaris',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->helperText('Pilih metode penghapusan barang'),
                            ])->columns(1)->collapsible(),
                        Forms\Components\Section::make('Persetujuan')
                            ->schema([
                                Forms\Components\Select::make('employee_id')
                                    ->relationship('employeeDisposals', 'name')
                                    ->label('Pejabat yang Mengetahui/Menyetujui')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pegawai yang berwenang menyetujui penghapusan'),
                                Forms\Components\Textarea::make('disposal_notes')
                                    ->label('Catatan Penghapusan')
                                    ->rows(2)
                                    ->placeholder('Catatan tambahan (opsional)...')
                                    ->columnSpanFull(),
                            ])->collapsible(),
                        Forms\Components\Section::make('Dokumen Pendukung')
                            ->schema([
                                Forms\Components\FileUpload::make('docs')
                                    ->directory('disposals')
                                    ->disk('public')
                                    ->label('Lampiran SK/Berita Acara')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->helperText('Upload surat keputusan atau berita acara penghapusan'),
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
                Tables\Columns\TextColumn::make('disposals_number')
                    ->label('No. Penghapusan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('disposal_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assetDisposals.assets_number')
                    ->label('No. Aset')
                    ->searchable(),
                Tables\Columns\TextColumn::make('assetDisposals.name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('book_value')
                    ->label('Nilai Buku')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('disposal_value')
                    ->label('Nilai Penghapusan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('disposal_process')
                    ->label('Proses')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'dimusnahkan' => 'danger',
                        'dijual' => 'success',
                        'dihibahkan' => 'info',
                        'dihapus dari inventaris' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucwords($state)),
                Tables\Columns\TextColumn::make('disposal_reason')
                    ->label('Alasan')
                    ->wrap()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('employeeDisposals.name')
                    ->label('Disetujui Oleh')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('disposal_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('disposal_process')
                    ->options([
                        'dimusnahkan' => 'Dimusnahkan',
                        'dijual' => 'Dijual',
                        'dihibahkan' => 'Dihibahkan',
                        'dihapus dari inventaris' => 'Dihapus dari Daftar Inventaris',
                    ])
                    ->label('Proses Penghapusan'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('cetak_sk')
                        ->label('Cetak SK Penghapusan')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->url(fn ($record) => route('disposal.cetak-sk', $record->id))
                        ->openUrlInNewTab(),
                ])
                    ->label('Actions')
                    ->button()
                    ->color('primary')
                    ->icon('heroicon-m-ellipsis-vertical')
            ])
            ->bulkActions([
                // Removed bulk delete - disposal records should be preserved
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
            'index' => Pages\ListAssetDisposals::route('/'),
            'create' => Pages\CreateAssetDisposal::route('/create'),
            'edit' => Pages\EditAssetDisposal::route('/{record}/edit'),
        ];
    }
}
