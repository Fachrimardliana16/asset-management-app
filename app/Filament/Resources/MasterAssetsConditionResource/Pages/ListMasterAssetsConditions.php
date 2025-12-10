<?php

namespace App\Filament\Resources\MasterAssetsConditionResource\Pages;

use App\Filament\Resources\MasterAssetsConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterAssetsConditions extends ListRecords
{
    protected static string $resource = MasterAssetsConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
