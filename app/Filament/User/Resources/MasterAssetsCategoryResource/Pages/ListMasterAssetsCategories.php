<?php

namespace App\Filament\User\Resources\MasterAssetsCategoryResource\Pages;

use App\Filament\User\Resources\MasterAssetsCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterAssetsCategories extends ListRecords
{
    protected static string $resource = MasterAssetsCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
