<?php

namespace App\Filament\Resources\AssetMonitoringResource\Pages;

use App\Filament\Resources\AssetMonitoringResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetMonitoring extends EditRecord
{
    protected static string $resource = AssetMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['users_id'] = auth()->id();

        return $data;
    }
}
