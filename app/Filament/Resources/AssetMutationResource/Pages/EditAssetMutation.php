<?php

namespace App\Filament\Resources\AssetMutationResource\Pages;

use App\Filament\Resources\AssetMutationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetMutation extends EditRecord
{
    protected static string $resource = AssetMutationResource::class;

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
