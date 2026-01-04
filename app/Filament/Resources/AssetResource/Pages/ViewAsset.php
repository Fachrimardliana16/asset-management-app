<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\RepeatableEntry;

class ViewAsset extends ViewRecord
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_profile')
                ->label('Cetak Profil Aset')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\CheckboxList::make('sections')
                        ->label('Pilih Section yang Akan Dicetak')
                        ->options([
                            'qr_code' => 'QR Code Aset',
                            'asset_info' => 'Informasi Aset',
                            'financial_info' => 'Informasi Keuangan',
                            'mutations' => 'Riwayat Mutasi',
                            'monitoring' => 'Riwayat Monitoring',
                            'maintenance' => 'Riwayat Pemeliharaan',
                            'taxes' => 'Riwayat Pembayaran Pajak',
                        ])
                        ->default(['qr_code', 'asset_info', 'financial_info', 'mutations', 'monitoring', 'maintenance', 'taxes'])
                        ->columns(2)
                        ->required()
                        ->minItems(1)
                        ->gridDirection('row'),
                ])
                ->action(function (array $data) {
                    $sections = implode(',', $data['sections']);
                    $url = route('export.asset-profile', ['id' => $this->record->id, 'sections' => $sections]);
                    $this->js("window.open('$url', '_blank')");
                }),
            Actions\Action::make('print_qrcode')
                ->label('Print QR Code')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->action(function () {
                    $this->js("window.open('" . route('asset.print-barcode', $this->record->id) . "', '_blank')");
                }),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('QR Code Aset')
                    ->description('QR Code untuk identifikasi aset')
                    ->schema([
                        ViewEntry::make('barcode')
                            ->view('filament.resources.asset-resource.barcode-view')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Informasi Aset')
                    ->description('Detail informasi aset')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                ImageEntry::make('img')
                                    ->label('Gambar Aset')
                                    ->height(150)
                                    ->defaultImageUrl(url('/images/no-image.png')),
                                TextEntry::make('assets_number')
                                    ->label('Nomor Aset')
                                    ->badge()
                                    ->color('primary')
                                    ->copyable()
                                    ->copyMessage('Nomor aset disalin!')
                                    ->weight('bold'),
                                TextEntry::make('name')
                                    ->label('Nama Aset')
                                    ->weight('bold'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('categoryAsset.name')
                                    ->label('Kategori')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('brand')
                                    ->label('Merk'),
                                TextEntry::make('assetsStatus.name')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'Aktif' => 'success',
                                        'Tidak Aktif' => 'danger',
                                        default => 'warning',
                                    }),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('conditionAsset.name')
                                    ->label('Kondisi')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'Baik' => 'success',
                                        'Rusak Ringan' => 'warning',
                                        'Rusak Berat' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('AssetTransactionStatus.name')
                                    ->label('Status Mutasi')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'Transaksi Keluar' => 'danger',
                                        'Transaksi Masuk' => 'success',
                                        default => 'gray',
                                    })
                                    ->placeholder('Di Gudang'),
                                TextEntry::make('purchase_date')
                                    ->label('Tanggal Pembelian')
                                    ->date('d F Y'),
                            ]),
                    ])
                    ->columns(3),

                Section::make('Informasi Keuangan')
                    ->description('Detail nilai dan sumber dana aset')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('price')
                                    ->label('Harga Beli')
                                    ->money('IDR'),
                                TextEntry::make('book_value')
                                    ->label('Nilai Buku')
                                    ->money('IDR'),
                                TextEntry::make('book_value_expiry')
                                    ->label('Habis Nilai Buku')
                                    ->date('d F Y')
                                    ->color(fn($record) => $record->book_value_expiry <= now() ? 'danger' : 'success'),
                                TextEntry::make('funding_source')
                                    ->label('Sumber Dana'),
                            ]),
                        TextEntry::make('book_value_status')
                            ->label('Status Nilai Buku')
                            ->state(fn($record) => $record->book_value_expiry <= now() ? 'Nilai Buku Sudah Habis - Pertimbangkan Penggantian' : 'Nilai Buku Masih Berlaku')
                            ->badge()
                            ->color(fn($record) => $record->book_value_expiry <= now() ? 'danger' : 'success'),
                    ])
                    ->collapsible(),

                Section::make('Keterangan')
                    ->schema([
                        TextEntry::make('desc')
                            ->label('Deskripsi')
                            ->placeholder('Tidak ada keterangan')
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime('d F Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label('Diperbarui')
                                    ->dateTime('d F Y H:i'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Informasi Mutasi')
                    ->description('Data mutasi barang')
                    ->schema([
                        // Mutasi Terakhir
                        TextEntry::make('latest_mutation_label')
                            ->label('')
                            ->default('Mutasi Terakhir')
                            ->weight('bold')
                            ->size('lg'),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('latestMutation.mutation_date')
                                    ->label('Tanggal Mutasi')
                                    ->date('d F Y')
                                    ->placeholder('Belum ada mutasi'),
                                TextEntry::make('latestMutation.AssetsMutationtransactionStatus.name')
                                    ->label('Jenis Mutasi')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'Transaksi Keluar' => 'danger',
                                        'Transaksi Masuk' => 'success',
                                        default => 'gray',
                                    })
                                    ->placeholder('-'),
                                TextEntry::make('latestMutation.AssetsMutationemployee.name')
                                    ->label('Pemegang')
                                    ->placeholder('-'),
                                TextEntry::make('latestMutation.AssetsMutationlocation.name')
                                    ->label('Lokasi')
                                    ->placeholder('-'),
                            ]),
                        TextEntry::make('latestMutation.desc')
                            ->label('Keterangan Mutasi')
                            ->placeholder('Tidak ada keterangan')
                            ->columnSpanFull(),

                        // Riwayat Mutasi
                        TextEntry::make('mutation_history_label')
                            ->label('')
                            ->default('Riwayat Mutasi')
                            ->weight('bold')
                            ->size('lg'),
                        RepeatableEntry::make('AssetsMutation')
                            ->label('')
                            ->state(fn($record) => $record->AssetsMutation()->latest()->limit(20)->get())
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        TextEntry::make('mutation_date')
                                            ->label('Tanggal')
                                            ->date('d F Y'),
                                        TextEntry::make('AssetsMutationtransactionStatus.name')
                                            ->label('Jenis')
                                            ->badge()
                                            ->color(fn($state) => match ($state) {
                                                'Transaksi Keluar' => 'danger',
                                                'Transaksi Masuk' => 'success',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('AssetsMutationemployee.name')
                                            ->label('Pemegang'),
                                        TextEntry::make('AssetsMutationlocation.name')
                                            ->label('Lokasi'),
                                        TextEntry::make('desc')
                                            ->label('Keterangan')
                                            ->placeholder('-'),
                                    ]),
                            ])
                            ->columns(1)
                            ->contained(true)
                            ->extraAttributes(['class' => 'text-xs']),
                        TextEntry::make('no_mutation')
                            ->label('')
                            ->default('Belum ada riwayat mutasi')
                            ->visible(fn($record) => $record->AssetsMutation->isEmpty()),
                    ])
                    ->collapsible(),

                Section::make('Riwayat Monitoring')
                    ->description('Daftar monitoring/pengecekan aset')
                    ->schema([
                        RepeatableEntry::make('assetMonitoring')
                            ->label('')
                            ->state(fn($record) => $record->assetMonitoring()->latest()->limit(15)->get())
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('monitoring_date')
                                            ->label('Tanggal Monitoring')
                                            ->date('d F Y'),
                                        TextEntry::make('MonitoringoldCondition.name')
                                            ->label('Kondisi Lama')
                                            ->badge()
                                            ->color(fn($state) => match ($state) {
                                                'Baik' => 'success',
                                                'Rusak Ringan' => 'warning',
                                                'Rusak Berat' => 'danger',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('MonitoringNewCondition.name')
                                            ->label('Kondisi Baru')
                                            ->badge()
                                            ->color(fn($state) => match ($state) {
                                                'Baik' => 'success',
                                                'Rusak Ringan' => 'warning',
                                                'Rusak Berat' => 'danger',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('user.name')
                                            ->label('Petugas'),
                                    ]),
                                TextEntry::make('desc')
                                    ->label('Keterangan')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                            ->contained(true),
                        TextEntry::make('no_monitoring')
                            ->label('')
                            ->default('Belum ada riwayat monitoring')
                            ->visible(fn($record) => $record->assetMonitoring->isEmpty()),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Riwayat Pemeliharaan')
                    ->description('Daftar pemeliharaan/perbaikan aset')
                    ->schema([
                        RepeatableEntry::make('AssetMaintenance')
                            ->label('')
                            ->state(fn($record) => $record->AssetMaintenance()->latest()->limit(15)->get())
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('maintenance_date')
                                            ->label('Tanggal Pemeliharaan')
                                            ->date('d F Y'),
                                        TextEntry::make('service_type')
                                            ->label('Jenis Perbaikan')
                                            ->badge()
                                            ->color(fn($state) => match ($state) {
                                                'Perbaikan Ringan' => 'info',
                                                'Perbaikan Berat' => 'warning',
                                                'Penggantian Komponen' => 'danger',
                                                'Perawatan Rutin' => 'success',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('location_service')
                                            ->label('Lokasi Perbaikan'),
                                        TextEntry::make('service_cost')
                                            ->label('Biaya')
                                            ->money('IDR'),
                                    ]),
                                TextEntry::make('desc')
                                    ->label('Keterangan')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                            ->contained(true),
                        TextEntry::make('no_maintenance')
                            ->label('')
                            ->default('Belum ada riwayat pemeliharaan')
                            ->visible(fn($record) => $record->AssetMaintenance->isEmpty()),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Riwayat Pembayaran Pajak')
                    ->description('Riwayat pembayaran pajak aset')
                    ->schema([
                        RepeatableEntry::make('taxes')
                            ->label('')
                            ->state(fn($record) => $record->taxes()->latest()->limit(10)->get())
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        TextEntry::make('taxType.name')
                                            ->label('Jenis Pajak')
                                            ->badge()
                                            ->color('info'),
                                        TextEntry::make('tax_year')
                                            ->label('Tahun Pajak')
                                            ->badge(),
                                        TextEntry::make('tax_amount')
                                            ->label('Nilai Pajak')
                                            ->money('IDR'),
                                        TextEntry::make('penalty_amount')
                                            ->label('Denda')
                                            ->money('IDR')
                                            ->color(fn($state) => $state > 0 ? 'warning' : 'gray'),
                                        TextEntry::make('payment_status')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn($state) => match ($state) {
                                                'paid' => 'success',
                                                'pending' => 'warning',
                                                'overdue' => 'danger',
                                                'cancelled' => 'gray',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn($state) => match ($state) {
                                                'paid' => 'Lunas',
                                                'pending' => 'Pending',
                                                'overdue' => 'Terlambat',
                                                'cancelled' => 'Batal',
                                                default => ucfirst($state),
                                            }),
                                    ]),
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('due_date')
                                            ->label('Jatuh Tempo')
                                            ->date('d F Y')
                                            ->color(fn($record) => $record->due_date < now() && $record->payment_status !== 'paid' ? 'danger' : 'gray'),
                                        TextEntry::make('payment_date')
                                            ->label('Tanggal Bayar')
                                            ->date('d F Y')
                                            ->placeholder('-'),
                                        TextEntry::make('total_payment')
                                            ->label('Total Dibayar')
                                            ->state(fn($record) => $record->tax_amount + $record->penalty_amount)
                                            ->money('IDR')
                                            ->weight('bold'),
                                    ]),
                                TextEntry::make('notes')
                                    ->label('Catatan')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                            ->contained(true),
                        TextEntry::make('no_tax')
                            ->label('')
                            ->default('Belum ada histori pajak')
                            ->visible(fn($record) => $record->taxes->isEmpty()),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
