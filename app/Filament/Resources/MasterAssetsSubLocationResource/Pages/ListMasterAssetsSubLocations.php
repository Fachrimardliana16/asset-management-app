<?php

namespace App\Filament\Resources\MasterAssetsSubLocationResource\Pages;

use App\Filament\Resources\MasterAssetsSubLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterAssetsSubLocations extends ListRecords
{
    protected static string $resource = MasterAssetsSubLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
