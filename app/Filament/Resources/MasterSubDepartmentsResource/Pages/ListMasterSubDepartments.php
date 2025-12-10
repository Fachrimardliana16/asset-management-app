<?php

namespace App\Filament\Resources\MasterSubDepartmentsResource\Pages;

use App\Filament\Resources\MasterSubDepartmentsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterSubDepartments extends ListRecords
{
    protected static string $resource = MasterSubDepartmentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
