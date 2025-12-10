<?php

namespace App\Filament\Resources\AssetRequestsResource\Pages;

use App\Filament\Resources\AssetRequestsResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAssetRequests extends EditRecord
{
    protected static string $resource = AssetRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Berhasil!')
                        ->body('Data permintaan barang berhasil dihapus.')
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil!')
            ->body('Data permintaan barang berhasil diperbarui.');
    }
}
