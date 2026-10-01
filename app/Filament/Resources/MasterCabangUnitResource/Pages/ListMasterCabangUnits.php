<?php

namespace App\Filament\Resources\MasterCabangUnitResource\Pages;

use App\Filament\Resources\MasterCabangUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterCabangUnits extends ListRecords
{
    protected static string $resource = MasterCabangUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
