<?php

namespace App\Filament\Resources\MasterAssetsTransactionStatusResource\Pages;

use App\Filament\Resources\MasterAssetsTransactionStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterAssetsTransactionStatuses extends ListRecords
{
    protected static string $resource = MasterAssetsTransactionStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
