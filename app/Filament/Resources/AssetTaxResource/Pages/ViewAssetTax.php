<?php

namespace App\Filament\Resources\AssetTaxResource\Pages;

use App\Filament\Resources\AssetTaxResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class ViewAssetTax extends ViewRecord
{
    protected static string $resource = AssetTaxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Aset')
                    ->schema([
                        Infolists\Components\TextEntry::make('asset.name')
                            ->label('Nama Aset'),
                        Infolists\Components\TextEntry::make('asset.asset_code')
                            ->label('Kode Aset'),
                        Infolists\Components\TextEntry::make('asset.category.name')
                            ->label('Kategori'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Informasi Pajak')
                    ->schema([
                        Infolists\Components\TextEntry::make('taxType.name')
                            ->label('Jenis Pajak')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('tax_year')
                            ->label('Tahun Pajak'),
                        Infolists\Components\TextEntry::make('tax_amount')
                            ->label('Nilai Pajak')
                            ->money('IDR')
                            ->weight(FontWeight::Bold),
                        Infolists\Components\TextEntry::make('penalty_amount')
                            ->label('Denda')
                            ->money('IDR')
                            ->color('danger')
                            ->weight(FontWeight::Bold),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Total Bayar')
                            ->money('IDR')
                            ->color('success')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Status & Tanggal')
                    ->schema([
                        Infolists\Components\TextEntry::make('due_date')
                            ->label('Jatuh Tempo')
                            ->date('d M Y')
                            ->badge()
                            ->color(fn ($record) => $record->isOverdue() ? 'danger' : 'success'),
                        Infolists\Components\TextEntry::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->date('d M Y')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('payment_status')
                            ->label('Status Pembayaran')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match($state) {
                                'paid' => 'Lunas',
                                'pending' => 'Pending',
                                'overdue' => 'Terlambat',
                                'cancelled' => 'Batal',
                                default => $state,
                            })
                            ->color(fn ($state) => match($state) {
                                'paid' => 'success',
                                'pending' => 'warning',
                                'overdue' => 'danger',
                                'cancelled' => 'secondary',
                                default => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('approval_status')
                            ->label('Status Approval')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match($state) {
                                'approved' => 'Disetujui',
                                'pending' => 'Pending',
                                'rejected' => 'Ditolak',
                                default => $state,
                            })
                            ->color(fn ($state) => match($state) {
                                'approved' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                default => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('overdue_days')
                            ->label('Hari Keterlambatan')
                            ->suffix(' hari')
                            ->color('danger')
                            ->visible(fn ($record) => $record->overdue_days > 0),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Kalkulasi Denda')
                    ->schema([
                        Infolists\Components\TextEntry::make('penalty_calculation')
                            ->label('Detail Perhitungan')
                            ->default('Tidak ada denda')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->penalty_amount > 0),

                Infolists\Components\Section::make('Catatan')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Catatan')
                            ->default('-')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->color('danger')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->approval_status === 'rejected'),
                    ]),

                Infolists\Components\Section::make('Informasi Approval')
                    ->schema([
                        Infolists\Components\TextEntry::make('paidByUser.name')
                            ->label('Dibayar Oleh')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('approvedByUser.name')
                            ->label('Disetujui Oleh')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('approved_at')
                            ->label('Tanggal Approval')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Timeline')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Terakhir Diupdate')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
