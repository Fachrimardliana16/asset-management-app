<?php

namespace App\Filament\Resources\AssetPurchaseResource\Pages;

use App\Filament\Resources\AssetPurchaseResource;
use App\Models\AssetPurchase;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Support\HtmlString;

class ViewAssetPurchase extends ViewRecord
{
    protected static string $resource = AssetPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetak_faktur')
                ->label('Cetak Faktur')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->visible(fn() => $this->record->purchase_status === 'purchased')
                ->url(fn() => route('purchase.invoice', ['record' => $this->record->id]))
                ->openUrlInNewTab(),

            Actions\EditAction::make()
                ->label('Edit'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Informasi Permintaan Barang')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('document_number')
                                    ->label('No. Dokumen')
                                    ->icon('heroicon-m-document-text')
                                    ->badge()
                                    ->color('primary'),

                                Components\TextEntry::make('date')
                                    ->label('Tanggal Permintaan')
                                    ->icon('heroicon-m-calendar')
                                    ->date('d F Y'),

                                Components\TextEntry::make('purchase_status')
                                    ->label('Status Pembelian')
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
                            ]),
                    ])
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible(),

                Components\Section::make('Pemohon & Departemen')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('requestedBy.name')
                                    ->label('Pemohon')
                                    ->icon('heroicon-m-user')
                                    ->badge()
                                    ->color('success'),

                                Components\TextEntry::make('department.name')
                                    ->label('Departemen')
                                    ->icon('heroicon-m-building-office-2')
                                    ->badge()
                                    ->color('info'),
                            ]),

                        Components\TextEntry::make('desc')
                            ->label('Keterangan Umum')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada keterangan'),
                    ])
                    ->icon('heroicon-o-users')
                    ->collapsible(),

                Components\Section::make('Detail Barang yang Diminta')
                    ->description('Daftar barang yang diminta untuk dibeli')
                    ->schema([
                        Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('asset_name')
                                    ->label('Nama Barang')
                                    ->icon('heroicon-m-cube')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('primary'),

                                Components\Grid::make(4)
                                    ->schema([
                                        Components\TextEntry::make('category.name')
                                            ->label('Kategori')
                                            ->icon('heroicon-m-tag')
                                            ->badge()
                                            ->color('warning'),

                                        Components\TextEntry::make('quantity')
                                            ->label('Jumlah')
                                            ->icon('heroicon-m-calculator')
                                            ->suffix(' unit')
                                            ->badge()
                                            ->color('success'),

                                        Components\TextEntry::make('location.name')
                                            ->label('Lokasi')
                                            ->icon('heroicon-m-building-office-2'),

                                        Components\TextEntry::make('subLocation.name')
                                            ->label('Sub Lokasi')
                                            ->icon('heroicon-m-map-pin')
                                            ->placeholder('Tidak ada sub lokasi'),
                                    ]),

                                Components\Grid::make(2)
                                    ->schema([
                                        Components\TextEntry::make('purpose')
                                            ->label('Keperluan')
                                            ->icon('heroicon-m-information-circle'),

                                        Components\TextEntry::make('notes')
                                            ->label('Catatan Item')
                                            ->placeholder('Tidak ada catatan'),
                                    ]),

                                Components\TextEntry::make('purchase_status')
                                    ->label('Status Pembelian Item')
                                    ->badge()
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'pending' => 'Menunggu',
                                        'partial' => 'Sebagian',
                                        'complete' => 'Selesai',
                                        default => 'Menunggu',
                                    })
                                    ->color(fn($state) => match ($state) {
                                        'pending' => 'warning',
                                        'partial' => 'info',
                                        'complete' => 'success',
                                        default => 'warning',
                                    })
                                    ->icon(fn($state) => match ($state) {
                                        'pending' => 'heroicon-o-clock',
                                        'partial' => 'heroicon-o-arrow-path',
                                        'complete' => 'heroicon-o-check-circle',
                                        default => 'heroicon-o-clock',
                                    }),
                            ])
                            ->columnSpanFull()
                            ->contained(true),

                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('total_items')
                                    ->label('Total Jenis Barang')
                                    ->suffix(' jenis')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-cube'),

                                Components\TextEntry::make('total_quantity')
                                    ->label('Total Unit')
                                    ->suffix(' unit')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-calculator'),
                            ]),
                    ])
                    ->icon('heroicon-o-cube')
                    ->collapsible(),

                Components\Section::make('Ringkasan Pembelian')
                    ->description('Total pembelian yang sudah diproses')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('purchased_items_count')
                                    ->label('Barang Terbeli')
                                    ->icon('heroicon-o-check-circle')
                                    ->badge()
                                    ->color('success')
                                    ->getStateUsing(function ($record) {
                                        $purchasedCount = $record->purchases()->count();
                                        $totalQuantity = $record->total_quantity;
                                        return "{$purchasedCount} / {$totalQuantity} unit";
                                    }),

                                Components\TextEntry::make('total_spent')
                                    ->label('Total Pengeluaran')
                                    ->icon('heroicon-m-currency-dollar')
                                    ->money('IDR')
                                    ->color('warning')
                                    ->weight('bold')
                                    ->getStateUsing(function ($record) {
                                        return $record->purchases()->sum('price');
                                    }),

                                Components\TextEntry::make('purchase_progress')
                                    ->label('Progress')
                                    ->icon('heroicon-o-chart-bar')
                                    ->badge()
                                    ->color(fn($record) => $record->purchase_progress >= 100 ? 'success' : 'info')
                                    ->getStateUsing(function ($record) {
                                        return "{$record->purchase_progress}%";
                                    }),
                            ]),

                        Components\TextEntry::make('purchase_notes')
                            ->label('Catatan Pembelian')
                            ->icon('heroicon-m-pencil-square')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada catatan'),
                    ])
                    ->icon('heroicon-o-shopping-cart')
                    ->visible(fn($record) => $record->purchases()->count() > 0)
                    ->collapsible(),

                Components\Section::make('Detail Aset yang Sudah Dibeli')
                    ->description('Daftar aset yang sudah dibeli dari permintaan ini')
                    ->schema([
                        Components\RepeatableEntry::make('purchases')
                            ->label('')
                            ->schema([
                                Components\Grid::make(4)
                                    ->schema([
                                        Components\TextEntry::make('assets_number')
                                            ->label('Nomor Aset')
                                            ->badge()
                                            ->color('primary')
                                            ->icon('heroicon-o-hashtag')
                                            ->weight('bold'),

                                        Components\TextEntry::make('brand')
                                            ->label('Merk/Tipe')
                                            ->icon('heroicon-m-sparkles')
                                            ->placeholder('-'),

                                        Components\TextEntry::make('price')
                                            ->label('Harga')
                                            ->money('IDR')
                                            ->icon('heroicon-m-currency-dollar')
                                            ->color('success')
                                            ->weight('bold'),

                                        Components\TextEntry::make('purchase_date')
                                            ->label('Tgl Pembelian')
                                            ->date('d M Y')
                                            ->icon('heroicon-m-calendar'),
                                    ]),

                                Components\Grid::make(3)
                                    ->schema([
                                        Components\TextEntry::make('funding_source')
                                            ->label('Sumber Dana')
                                            ->icon('heroicon-m-banknotes')
                                            ->placeholder('-'),

                                        Components\TextEntry::make('condition.name')
                                            ->label('Kondisi')
                                            ->badge()
                                            ->icon('heroicon-m-shield-check')
                                            ->placeholder('-'),

                                        Components\TextEntry::make('status.name')
                                            ->label('Status')
                                            ->badge()
                                            ->color('success')
                                            ->icon('heroicon-m-check-badge')
                                            ->placeholder('-'),
                                    ]),

                                Components\ImageEntry::make('img')
                                    ->label('Foto Aset')
                                    ->height(150)
                                    ->visible(fn($record) => $record->img !== null),
                            ])
                            ->columnSpanFull()
                            ->contained(true),
                    ])
                    ->icon('heroicon-o-queue-list')
                    ->visible(fn($record) => $record->purchases()->count() > 0)
                    ->collapsible(),

                Components\Section::make('Lampiran Permintaan')
                    ->schema([
                        Components\ImageEntry::make('docs')
                            ->label('Dokumen Lampiran')
                            ->height(200)
                            ->columnSpanFull(),
                    ])
                    ->icon('heroicon-o-photo')
                    ->visible(fn($record) => $record->docs !== null)
                    ->collapsible()
                    ->collapsed(),

                Components\Section::make('Informasi Sistem')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->icon('heroicon-m-clock')
                                    ->dateTime('d F Y, H:i'),

                                Components\TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->icon('heroicon-m-arrow-path')
                                    ->dateTime('d F Y, H:i'),
                            ]),
                    ])
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
