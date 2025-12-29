<?php

namespace App\Filament\Resources\AssetTaxResource\Pages;

use App\Filament\Resources\AssetTaxResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAssetTax extends CreateRecord
{
    protected static string $resource = AssetTaxResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['paid_by'] = Auth::id();
        
        return $data;
    }
}
