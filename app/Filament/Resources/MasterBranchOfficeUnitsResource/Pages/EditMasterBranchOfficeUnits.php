<?php

namespace App\Filament\Resources\MasterBranchOfficeUnitsResource\Pages;

use App\Filament\Resources\MasterBranchOfficeUnitsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterBranchOfficeUnits extends EditRecord
{
    protected static string $resource = MasterBranchOfficeUnitsResource::class;

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
