<?php

namespace App\Filament\Resources\MasterAssetsStatusResource\Pages;

use App\Filament\Resources\MasterAssetsStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterAssetsStatuses extends ListRecords
{
    protected static string $resource = MasterAssetsStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
