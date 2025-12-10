<?php

namespace App\Filament\Resources\MasterAssetsLocationResource\Pages;

use App\Filament\Resources\MasterAssetsLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterAssetsLocation extends EditRecord
{
    protected static string $resource = MasterAssetsLocationResource::class;

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
