<?php

namespace App\Filament\Resources\MasterAssetsLocationResource\Pages;

use App\Filament\Resources\MasterAssetsLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterAssetsLocations extends ListRecords
{
    protected static string $resource = MasterAssetsLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
