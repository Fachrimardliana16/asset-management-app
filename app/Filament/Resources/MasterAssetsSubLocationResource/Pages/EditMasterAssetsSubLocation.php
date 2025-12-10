<?php

namespace App\Filament\Resources\MasterAssetsSubLocationResource\Pages;

use App\Filament\Resources\MasterAssetsSubLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterAssetsSubLocation extends EditRecord
{
    protected static string $resource = MasterAssetsSubLocationResource::class;

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
