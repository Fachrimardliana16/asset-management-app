<?php

namespace App\Filament\User\Resources\AssetDisposalResource\Pages;

use App\Filament\User\Resources\AssetDisposalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetDisposal extends EditRecord
{
    protected static string $resource = AssetDisposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
