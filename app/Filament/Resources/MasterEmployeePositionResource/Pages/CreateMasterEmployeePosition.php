<?php

namespace App\Filament\Resources\MasterEmployeePositionResource\Pages;

use App\Filament\Resources\MasterEmployeePositionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterEmployeePosition extends CreateRecord
{
    protected static string $resource = MasterEmployeePositionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
