<?php

namespace App\Filament\Resources\MasterAssetsComplaintStatusResource\Pages;

use App\Filament\Resources\MasterAssetsComplaintStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterAssetsComplaintStatuses extends ListRecords
{
    protected static string $resource = MasterAssetsComplaintStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
