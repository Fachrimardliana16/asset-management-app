<?php

namespace App\Filament\Resources\MasterAssetsCategoryResource\Pages;

use App\Filament\Resources\MasterAssetsCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterAssetsCategory extends EditRecord
{
    protected static string $resource = MasterAssetsCategoryResource::class;

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
