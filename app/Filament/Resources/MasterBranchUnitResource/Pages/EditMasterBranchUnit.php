<?php

namespace App\Filament\Resources\MasterBranchUnitResource\Pages;

use App\Filament\Resources\MasterBranchUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterBranchUnit extends EditRecord
{
    protected static string $resource = MasterBranchUnitResource::class;

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
