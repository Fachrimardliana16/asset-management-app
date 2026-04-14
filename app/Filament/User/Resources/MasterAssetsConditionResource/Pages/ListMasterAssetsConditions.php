<?php

namespace App\Filament\User\Resources\MasterAssetsConditionResource\Pages;

use App\Filament\User\Resources\MasterAssetsConditionResource;
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
