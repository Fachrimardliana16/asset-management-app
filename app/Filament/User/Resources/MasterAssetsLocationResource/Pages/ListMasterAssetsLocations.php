<?php

namespace App\Filament\User\Resources\MasterAssetsLocationResource\Pages;

use App\Filament\User\Resources\MasterAssetsLocationResource;
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
