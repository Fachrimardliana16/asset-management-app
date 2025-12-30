<?php

namespace App\Filament\Resources\MasterTaxTypeResource\Pages;

use App\Filament\Resources\MasterTaxTypeResource;
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
