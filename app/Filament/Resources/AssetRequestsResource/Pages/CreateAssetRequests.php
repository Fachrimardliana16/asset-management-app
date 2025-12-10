<?php

namespace App\Filament\Resources\AssetRequestsResource\Pages;

use App\Filament\Resources\AssetRequestsResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetRequests extends CreateRecord
{
    protected static string $resource = AssetRequestsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil!')
            ->body('Data permintaan barang berhasil ditambahkan.');
    }
}
