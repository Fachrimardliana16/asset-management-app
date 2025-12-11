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

                Components\Section::make('Detail Barang')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('asset_name')
                                    ->label('Nama Barang')
                                    ->icon('heroicon-m-cube')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->columnSpan(2),

                                Components\TextEntry::make('quantity')
                                    ->label('Jumlah')
                                    ->icon('heroicon-m-calculator')
                                    ->suffix(' unit')
                                    ->badge()
                                    ->color('info'),
                            ]),

                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('category.name')
                                    ->label('Kategori')
                                    ->icon('heroicon-m-tag')
                                    ->badge()
                                    ->color('success')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->category?->name . ' (' . $record->category?->kode . ')'
                                    ),

                                Components\TextEntry::make('purpose')
                                    ->label('Keperluan')
                                    ->icon('heroicon-m-information-circle'),
                            ]),

                        Components\TextEntry::make('desc')
                            ->label('Keterangan')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada keterangan'),
                    ])
                    ->icon('heroicon-o-cube')
                    ->collapsible(),

                Components\Section::make('Pemohon & Lokasi')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('employee.name')
                                    ->label('Pemohon')
                                    ->icon('heroicon-m-user'),

                                Components\TextEntry::make('location.name')
                                    ->label('Lokasi')
                                    ->icon('heroicon-m-building-office-2')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->location?->name . ' (' . $record->location?->kode . ')'
                                    ),
                            ]),

                        Components\TextEntry::make('subLocation.name')
                            ->label('Sub Lokasi')
                            ->icon('heroicon-m-map-pin')
                            ->placeholder('Tidak ada sub lokasi'),
                    ])
                    ->icon('heroicon-o-users')
                    ->collapsible(),

                Components\Section::make('Informasi Pembelian')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('purchase_date')
                                    ->label('Tanggal Pembelian')
                                    ->icon('heroicon-m-calendar-days')
                                    ->date('d F Y')
                                    ->placeholder('Belum dibeli')
                                    ->getStateUsing(function ($record) {
                                        if ($record->purchase_status === 'purchased') {
                                            $purchase = AssetPurchase::where('assetrequest_id', $record->id)->first();
                                            return $purchase?->purchase_date;
                                        }
                                        return null;
                                    }),

                                Components\TextEntry::make('brand')
                                    ->label('Merk / Tipe')
                                    ->icon('heroicon-m-sparkles')
                                    ->placeholder('Belum dibeli')
                                    ->getStateUsing(function ($record) {
                                        if ($record->purchase_status === 'purchased') {
                                            $purchase = AssetPurchase::where('assetrequest_id', $record->id)->first();
                                            return $purchase?->brand;
                                        }
                                        return null;
                                    }),

                                Components\TextEntry::make('funding_source')
                                    ->label('Sumber Dana')
                                    ->icon('heroicon-m-banknotes')
                                    ->placeholder('Belum dibeli')
                                    ->getStateUsing(function ($record) {
                                        if ($record->purchase_status === 'purchased') {
                                            $purchase = AssetPurchase::where('assetrequest_id', $record->id)->first();
                                            return $purchase?->funding_source;
                                        }
                                        return null;
                                    }),
                            ]),

                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('price')
                                    ->label('Harga Satuan')
                                    ->icon('heroicon-m-currency-dollar')
                                    ->money('IDR')
                                    ->placeholder('Belum dibeli')
                                    ->color('success')
                                    ->weight('bold')
                                    ->getStateUsing(function ($record) {
                                        if ($record->purchase_status === 'purchased') {
                                            $purchase = AssetPurchase::where('assetrequest_id', $record->id)->first();
                                            return $purchase?->price;
                                        }
                                        return null;
                                    }),

                                Components\TextEntry::make('total_price')
                                    ->label('Total Harga')
                                    ->icon('heroicon-m-calculator')
                                    ->money('IDR')
                                    ->placeholder('Belum dibeli')
                                    ->color('warning')
                                    ->weight('bold')
                                    ->getStateUsing(function ($record) {
                                        if ($record->purchase_status === 'purchased') {
                                            $purchase = AssetPurchase::where('assetrequest_id', $record->id)->first();
                                            return $purchase ? ($purchase->price * $record->quantity) : null;
                                        }
                                        return null;
                                    }),

                                Components\TextEntry::make('book_value')
                                    ->label('Nilai Buku')
                                    ->icon('heroicon-m-book-open')
                                    ->money('IDR')
                                    ->placeholder('Belum dibeli')
                                    ->getStateUsing(function ($record) {
                                        if ($record->purchase_status === 'purchased') {
                                            $purchase = AssetPurchase::where('assetrequest_id', $record->id)->first();
                                            return $purchase?->book_value;
                                        }
                                        return null;
                                    }),
                            ]),

                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('condition.name')
                                    ->label('Kondisi Aset')
                                    ->icon('heroicon-m-shield-check')
                                    ->badge()
                                    ->placeholder('Belum dibeli')
                                    ->getStateUsing(function ($record) {
                                        if ($record->purchase_status === 'purchased') {
                                            $purchase = AssetPurchase::where('assetrequest_id', $record->id)->first();
                                            return $purchase?->condition?->name;
                                        }
                                        return null;
                                    }),

                                Components\TextEntry::make('status.name')
                                    ->label('Status Aset')
                                    ->icon('heroicon-m-check-badge')
                                    ->badge()
                                    ->color('success')
                                    ->placeholder('Belum dibeli')
                                    ->getStateUsing(function ($record) {
                                        if ($record->purchase_status === 'purchased') {
                                            $purchase = AssetPurchase::where('assetrequest_id', $record->id)->first();
                                            return $purchase?->status?->name;
                                        }
                                        return null;
                                    }),
                            ]),

                        Components\TextEntry::make('purchase_notes')
                            ->label('Catatan Pembelian')
                            ->icon('heroicon-m-pencil-square')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada catatan')
                            ->getStateUsing(function ($record) {
                                return $record->purchase_notes;
                            }),
                    ])
                    ->icon('heroicon-o-shopping-cart')
                    ->visible(fn($record) => $record->purchase_status === 'purchased')
                    ->collapsible(),

                Components\Section::make('Aset yang Dibuat')
                    ->schema([
                        Components\TextEntry::make('asset_numbers')
                            ->label('Nomor Aset')
                            ->html()
                            ->getStateUsing(function ($record) {
                                if ($record->purchase_status === 'purchased') {
                                    $purchases = AssetPurchase::where('assetrequest_id', $record->id)
                                        ->orderBy('item_index')
                                        ->get();

                                    $html = '<div class="space-y-1">';
                                    foreach ($purchases as $purchase) {
                                        $html .= '<div class="flex items-center gap-2">';
                                        $html .= '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded text-primary-700 bg-primary-50">';
                                        $html .= $purchase->assets_number;
                                        $html .= '</span>';
                                        $html .= '</div>';
                                    }
                                    $html .= '</div>';
                                    return new HtmlString($html);
                                }
                                return 'Belum ada aset yang dibuat';
                            })
                            ->columnSpanFull(),
                    ])
                    ->icon('heroicon-o-queue-list')
                    ->visible(fn($record) => $record->purchase_status === 'purchased')
                    ->collapsible(),

                Components\Section::make('Lampiran')
                    ->schema([
                        Components\ImageEntry::make('docs')
                            ->label('Dokumen Lampiran')
                            ->columnSpanFull()
                            ->visible(fn($record) => $record->docs !== null),

                        Components\ImageEntry::make('img')
                            ->label('Foto Aset')
                            ->columnSpanFull()
                            ->visible(function ($record) {
                                if ($record->purchase_status === 'purchased') {
                                    $purchase = AssetPurchase::where('assetrequest_id', $record->id)->first();
                                    return $purchase?->img !== null;
                                }
                                return false;
                            })
                            ->getStateUsing(function ($record) {
                                if ($record->purchase_status === 'purchased') {
                                    $purchase = AssetPurchase::where('assetrequest_id', $record->id)->first();
                                    return $purchase?->img;
                                }
                                return null;
                            }),
                    ])
                    ->icon('heroicon-o-photo')
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
