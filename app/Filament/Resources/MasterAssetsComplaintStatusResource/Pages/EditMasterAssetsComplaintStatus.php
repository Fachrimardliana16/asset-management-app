<?php

namespace App\Filament\Resources\MasterAssetsComplaintStatusResource\Pages;

use App\Filament\Resources\MasterAssetsComplaintStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterAssetsComplaintStatus extends EditRecord
{
    protected static string $resource = MasterAssetsComplaintStatusResource::class;

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
