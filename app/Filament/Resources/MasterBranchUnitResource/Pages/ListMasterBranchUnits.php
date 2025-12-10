<?php

namespace App\Filament\Resources\MasterBranchUnitResource\Pages;

use App\Filament\Resources\MasterBranchUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterBranchUnits extends ListRecords
{
    protected static string $resource = MasterBranchUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
