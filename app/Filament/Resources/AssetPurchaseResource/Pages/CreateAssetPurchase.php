<?php

namespace App\Filament\Resources\AssetPurchaseResource\Pages;

use App\Filament\Resources\AssetPurchaseResource;
use App\Models\AssetRequests;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetPurchase extends CreateRecord
{
    protected static string $resource = AssetPurchaseResource::class;

    protected function afterCreate(): void
    {
        // Update status permintaan menjadi "in_progress" ketika pembelian dibuat
        $assetRequestId = $this->record->assetrequest_id;
        
        if ($assetRequestId) {
            $assetRequest = AssetRequests::find($assetRequestId);
            if ($assetRequest && $assetRequest->purchase_status === 'pending') {
                $assetRequest->update([
                    'purchase_status' => 'in_progress',
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil!')
            ->body('Data pembelian barang berhasil disimpan. Status permintaan telah diperbarui menjadi "Sedang Diproses".');
    }
}
