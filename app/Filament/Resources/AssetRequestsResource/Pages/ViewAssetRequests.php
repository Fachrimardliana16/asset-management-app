<?php

namespace App\Filament\Resources\AssetRequestsResource\Pages;

use App\Filament\Resources\AssetRequestsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Split;
use Filament\Support\Enums\FontWeight;

class ViewAssetRequests extends ViewRecord
{
    protected static string $resource = AssetRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit'),
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Umum Permintaan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('document_number')
                                    ->label('Nomor DBP')
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-o-document-text'),

                                TextEntry::make('date')
                                    ->label('Tanggal Permintaan')
                                    ->date('d F Y')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('department.name')
                                    ->label('Departemen')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-building-office'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('requestedBy.name')
                                    ->label('Pemohon')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('desc')
                                    ->label('Keterangan Umum')
                                    ->placeholder('Tidak ada keterangan'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Daftar Barang yang Diminta')
                    ->description('Detail barang yang diminta')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('asset_name')
                                    ->label('Nama Barang')
                                    ->weight(FontWeight::Bold)
                                    ->size('lg')
                                    ->color('primary')
                                    ->icon('heroicon-o-cube'),

                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('category.name')
                                            ->label('Kategori')
                                            ->badge()
                                            ->color('warning')
                                            ->icon('heroicon-o-tag'),

                                        TextEntry::make('quantity')
                                            ->label('Jumlah')
                                            ->suffix(' unit')
                                            ->badge()
                                            ->color('success')
                                            ->icon('heroicon-o-calculator'),

                                        TextEntry::make('location.name')
                                            ->label('Lokasi')
                                            ->icon('heroicon-o-map-pin'),

                                        TextEntry::make('subLocation.name')
                                            ->label('Sub Lokasi')
                                            ->placeholder('Tidak ada sub lokasi')
                                            ->icon('heroicon-o-map'),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('purpose')
                                            ->label('Keperluan')
                                            ->icon('heroicon-o-clipboard-document-check')
                                            ->columnSpan(1),

                                        TextEntry::make('notes')
                                            ->label('Catatan Item')
                                            ->placeholder('Tidak ada catatan')
                                            ->columnSpan(1),
                                    ]),

                                TextEntry::make('purchase_status')
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

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('total_items')
                                    ->label('Total Jenis Barang')
                                    ->suffix(' jenis')
                                    ->badge()
                                    ->color('info')
                                    ->size('lg'),

                                TextEntry::make('total_quantity')
                                    ->label('Total Unit')
                                    ->suffix(' unit')
                                    ->badge()
                                    ->color('success')
                                    ->size('lg'),

                                TextEntry::make('purchase_status')
                                    ->label('Status Pembelian Keseluruhan')
                                    ->badge()
                                    ->size('lg')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'pending' => 'Menunggu',
                                        'in_progress' => 'Sedang Diproses',
                                        'purchased' => 'Sudah Dibeli',
                                        'cancelled' => 'Dibatalkan',
                                        default => 'Menunggu',
                                    })
                                    ->color(fn($state) => match ($state) {
                                        'pending' => 'warning',
                                        'in_progress' => 'info',
                                        'purchased' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'warning',
                                    }),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Status Pengesahan')
                    ->description('Status persetujuan dari pejabat terkait')
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                IconEntry::make('kepala_sub_bagian')
                                    ->label('Kepala Sub Bagian')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                                IconEntry::make('kepala_bagian_umum')
                                    ->label('Kepala Bagian Umum')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                                IconEntry::make('kepala_bagian_keuangan')
                                    ->label('Kepala Bagian Keuangan')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                                IconEntry::make('direktur_umum')
                                    ->label('Direktur Umum')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                                IconEntry::make('direktur_utama')
                                    ->label('Direktur Utama')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                            ]),

                        ImageEntry::make('docs')
                            ->label('Bukti Lampiran')
                            ->height(200)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Informasi Pembuat')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('user.firstname')
                                    ->label('Diajukan Oleh')
                                    ->formatStateUsing(fn($record) => $record->user ? $record->user->firstname . ' ' . $record->user->lastname : '-')
                                    ->icon('heroicon-o-user-circle'),

                                TextEntry::make('created_at')
                                    ->label('Tanggal Diajukan')
                                    ->dateTime('d F Y H:i')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->dateTime('d F Y H:i')
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
