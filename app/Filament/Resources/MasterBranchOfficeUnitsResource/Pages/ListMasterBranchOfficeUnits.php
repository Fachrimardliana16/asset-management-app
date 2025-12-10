<?php

namespace App\Filament\Resources\MasterBranchOfficeUnitsResource\Pages;

use App\Filament\Resources\MasterBranchOfficeUnitsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterBranchOfficeUnits extends ListRecords
{
    protected static string $resource = MasterBranchOfficeUnitsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
