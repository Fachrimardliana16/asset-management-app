<?php

namespace App\Filament\Resources\MasterAssetsTransactionStatusResource\Pages;

use App\Filament\Resources\MasterAssetsTransactionStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterAssetsTransactionStatus extends EditRecord
{
    protected static string $resource = MasterAssetsTransactionStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['users_id'] = auth()->id();

        return $data;
    }
}
