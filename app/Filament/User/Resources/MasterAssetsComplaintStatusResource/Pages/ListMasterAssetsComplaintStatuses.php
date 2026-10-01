<?php

namespace App\Filament\User\Resources\MasterAssetsComplaintStatusResource\Pages;

use App\Filament\User\Resources\MasterAssetsComplaintStatusResource;
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
