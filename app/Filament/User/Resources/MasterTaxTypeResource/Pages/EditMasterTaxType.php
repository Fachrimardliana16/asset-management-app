<?php

namespace App\Filament\User\Resources\MasterTaxTypeResource\Pages;

use App\Filament\User\Resources\MasterTaxTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterTaxType extends EditRecord
{
    protected static string $resource = MasterTaxTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
