<?php

namespace App\Filament\Resources\AssetTaxResource\Pages;

use App\Filament\Resources\AssetTaxResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetTax extends EditRecord
{
    protected static string $resource = AssetTaxResource::class;

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

    protected function afterSave(): void
    {
        // Update penalty jika ada perubahan
        $this->record->updatePenalty();
    }
}
