<?php

namespace App\Filament\Resources\MasterAssetsConditionResource\Pages;

use App\Filament\Resources\MasterAssetsConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterAssetsCondition extends EditRecord
{
    protected static string $resource = MasterAssetsConditionResource::class;

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
