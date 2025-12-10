<?php

namespace App\Filament\Resources\MasterDepartmentsResource\Pages;

use App\Filament\Resources\MasterDepartmentsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterDepartments extends EditRecord
{
    protected static string $resource = MasterDepartmentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
