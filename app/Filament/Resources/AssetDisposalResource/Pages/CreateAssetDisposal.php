<?php

namespace App\Filament\Resources\AssetDisposalResource\Pages;

use App\Filament\Resources\AssetDisposalResource;
use App\Models\Asset;
use App\Models\MasterAssetsStatus;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetDisposal extends CreateRecord
{
    protected static string $resource = AssetDisposalResource::class;

    protected function afterCreate(): void
    {
        // Get the asset and update its status to Inactive
        $asset = Asset::find($this->record->assets_id);

        if ($asset) {
            $inactiveStatus = MasterAssetsStatus::where('name', 'Inactive')->first();

            if ($inactiveStatus) {
                $asset->update(['status_id' => $inactiveStatus->id]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
