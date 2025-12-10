<?php

namespace App\Filament\Resources\AssetMutationResource\Pages;

use App\Filament\Resources\AssetMutationResource;
use App\Models\Asset;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetMutation extends CreateRecord
{
    protected static string $resource = AssetMutationResource::class;

    protected function afterCreate(): void
    {
        // Update transaction_status_id pada aset sesuai jenis mutasi
        $asset = Asset::find($this->record->assets_id);

        if ($asset) {
            // Set transaction_status_id sama dengan jenis mutasi yang dipilih
            $asset->update([
                'transaction_status_id' => $this->record->transaction_status_id
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
