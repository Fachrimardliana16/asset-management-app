<?php

namespace App\Filament\Resources\MasterEmployeePositionResource\Pages;

use App\Filament\Resources\MasterEmployeePositionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListMasterEmployeePositions extends ListRecords
{
    protected static string $resource = MasterEmployeePositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
