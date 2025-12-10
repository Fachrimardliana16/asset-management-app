<?php

namespace App\Filament\Resources\MasterBranchOfficeResource\Pages;

use App\Filament\Resources\MasterBranchOfficeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterBranchOffice extends EditRecord
{
    protected static string $resource = MasterBranchOfficeResource::class;

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
