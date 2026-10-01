<?php

namespace App\Filament\User\Resources\MasterTaxTypeResource\Pages;

use App\Filament\User\Resources\MasterTaxTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterTaxType extends CreateRecord
{
    protected static string $resource = MasterTaxTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
