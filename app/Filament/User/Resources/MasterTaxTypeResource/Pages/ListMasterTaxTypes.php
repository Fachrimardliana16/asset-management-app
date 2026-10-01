<?php

namespace App\Filament\User\Resources\MasterTaxTypeResource\Pages;

use App\Filament\User\Resources\MasterTaxTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterTaxTypes extends ListRecords
{
    protected static string $resource = MasterTaxTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
