<?php

namespace App\Filament\Resources\MasterBranchOfficeResource\Pages;

use App\Filament\Resources\MasterBranchOfficeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterBranchOffices extends ListRecords
{
    protected static string $resource = MasterBranchOfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
