<?php

namespace App\Filament\Resources\MasterTaxTypeResource\Pages;

use App\Filament\Resources\MasterTaxTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterTaxType extends CreateRecord
{
    protected static string $resource = MasterTaxTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
