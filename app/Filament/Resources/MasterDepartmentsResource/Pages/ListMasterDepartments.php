<?php

namespace App\Filament\Resources\MasterDepartmentsResource\Pages;

use App\Filament\Resources\MasterDepartmentsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterDepartments extends ListRecords
{
    protected static string $resource = MasterDepartmentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
