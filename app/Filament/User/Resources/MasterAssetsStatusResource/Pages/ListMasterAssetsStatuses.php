<?php

namespace App\Filament\User\Resources\MasterAssetsStatusResource\Pages;

use App\Filament\User\Resources\MasterAssetsStatusResource;
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
