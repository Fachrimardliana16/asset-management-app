<?php

namespace App\Filament\Exports;

use App\Models\AssetTax;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AssetTaxExporter extends Exporter
{
    protected static ?string $model = AssetTax::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('asset.name')
                ->label('Nama Aset'),
            ExportColumn::make('asset.asset_code')
                ->label('Kode Aset'),
            ExportColumn::make('asset.category.name')
                ->label('Kategori Aset'),
            ExportColumn::make('taxType.name')
                ->label('Jenis Pajak'),
            ExportColumn::make('taxType.code')
                ->label('Kode Pajak'),
            ExportColumn::make('tax_year')
                ->label('Tahun Pajak'),
            ExportColumn::make('tax_amount')
                ->label('Nilai Pajak'),
            ExportColumn::make('penalty_amount')
                ->label('Denda'),
            ExportColumn::make('total_amount')
                ->label('Total'),
            ExportColumn::make('due_date')
                ->label('Jatuh Tempo'),
            ExportColumn::make('payment_date')
                ->label('Tanggal Pembayaran'),
            ExportColumn::make('payment_status')
                ->label('Status Pembayaran')
                ->formatStateUsing(fn ($state) => match($state) {
                    'paid' => 'Lunas',
                    'pending' => 'Pending',
                    'overdue' => 'Terlambat',
                    'cancelled' => 'Batal',
                    default => $state,
                }),
            ExportColumn::make('approval_status')
                ->label('Status Approval')
                ->formatStateUsing(fn ($state) => match($state) {
                    'approved' => 'Disetujui',
                    'pending' => 'Pending',
                    'rejected' => 'Ditolak',
                    default => $state,
                }),
            ExportColumn::make('overdue_days')
                ->label('Hari Keterlambatan'),
            ExportColumn::make('penalty_calculation')
                ->label('Kalkulasi Denda'),
            ExportColumn::make('notes')
                ->label('Catatan'),
            ExportColumn::make('rejection_reason')
                ->label('Alasan Penolakan'),
            ExportColumn::make('paidByUser.name')
                ->label('Dibayar Oleh'),
            ExportColumn::make('approvedByUser.name')
                ->label('Disetujui Oleh'),
            ExportColumn::make('approved_at')
                ->label('Tanggal Approval'),
            ExportColumn::make('created_at')
                ->label('Dibuat'),
            ExportColumn::make('updated_at')
                ->label('Diupdate'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export data pajak aset telah selesai dan ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' berhasil di-export.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' gagal di-export.';
        }

        return $body;
    }
}
