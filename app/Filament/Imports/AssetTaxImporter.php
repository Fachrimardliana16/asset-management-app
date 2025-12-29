<?php

namespace App\Filament\Imports;

use App\Models\AssetTax;
use App\Models\Asset;
use App\Models\MasterTaxType;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class AssetTaxImporter extends Importer
{
    protected static ?string $model = AssetTax::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('asset_code')
                ->label('Kode Aset')
                ->requiredMapping()
                ->rules(['required'])
                ->example('AST-2024-001'),
            
            ImportColumn::make('tax_type_code')
                ->label('Kode Jenis Pajak')
                ->requiredMapping()
                ->rules(['required'])
                ->example('PKB'),
            
            ImportColumn::make('tax_year')
                ->label('Tahun Pajak')
                ->requiredMapping()
                ->rules(['required', 'integer', 'min:2000', 'max:2100'])
                ->example('2024'),
            
            ImportColumn::make('tax_amount')
                ->label('Nilai Pajak')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0'])
                ->example('500000'),
            
            ImportColumn::make('due_date')
                ->label('Tanggal Jatuh Tempo')
                ->requiredMapping()
                ->rules(['required', 'date'])
                ->example('2024-12-31'),
            
            ImportColumn::make('payment_date')
                ->label('Tanggal Pembayaran')
                ->rules(['nullable', 'date'])
                ->example('2024-12-25'),
            
            ImportColumn::make('payment_status')
                ->label('Status Pembayaran')
                ->rules(['nullable', 'in:pending,paid,overdue,cancelled'])
                ->example('pending'),
            
            ImportColumn::make('notes')
                ->label('Catatan')
                ->rules(['nullable', 'string'])
                ->example('Pembayaran pajak tahunan'),
        ];
    }

    public function resolveRecord(): ?AssetTax
    {
        // Find asset by code
        $asset = Asset::where('asset_code', $this->data['asset_code'])
            ->orWhere('assets_number', $this->data['asset_code'])
            ->first();

        if (!$asset) {
            $this->logError("Aset dengan kode {$this->data['asset_code']} tidak ditemukan");
            return null;
        }

        // Find tax type by code
        $taxType = MasterTaxType::where('code', $this->data['tax_type_code'])
            ->orWhere('name', $this->data['tax_type_code'])
            ->first();

        if (!$taxType) {
            $this->logError("Jenis pajak dengan kode {$this->data['tax_type_code']} tidak ditemukan");
            return null;
        }

        // Check if tax already exists
        $existing = AssetTax::where('asset_id', $asset->id)
            ->where('tax_type_id', $taxType->id)
            ->where('tax_year', $this->data['tax_year'])
            ->first();

        if ($existing) {
            // Update existing
            return $existing;
        }

        // Create new
        return new AssetTax([
            'asset_id' => $asset->id,
            'tax_type_id' => $taxType->id,
        ]);
    }

    protected function beforeFill(): void
    {
        // Process payment status
        if (isset($this->data['payment_status'])) {
            $statusMap = [
                'lunas' => 'paid',
                'pending' => 'pending',
                'terlambat' => 'overdue',
                'batal' => 'cancelled',
            ];
            
            $status = strtolower($this->data['payment_status']);
            $this->data['payment_status'] = $statusMap[$status] ?? $status;
        }

        // Set default values
        if (empty($this->data['payment_status'])) {
            $this->data['payment_status'] = 'pending';
        }

        if (empty($this->data['approval_status'])) {
            $this->data['approval_status'] = 'pending';
        }

        // Set paid_by to current user
        $this->data['paid_by'] = auth()->id();
    }

    protected function afterSave(): void
    {
        // Calculate penalty if overdue
        if ($this->record->isOverdue()) {
            $this->record->updatePenalty();
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import data pajak aset telah selesai dan ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' berhasil di-import.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' gagal di-import.';
        }

        return $body;
    }
}
