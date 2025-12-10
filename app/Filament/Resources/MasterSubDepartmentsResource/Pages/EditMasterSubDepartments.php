<?php

namespace App\Filament\Resources\MasterSubDepartmentsResource\Pages;

use App\Filament\Resources\MasterSubDepartmentsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterSubDepartments extends EditRecord
{
    protected static string $resource = MasterSubDepartmentsResource::class;

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
