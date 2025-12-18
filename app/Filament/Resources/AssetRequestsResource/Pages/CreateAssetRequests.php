<?php

namespace App\Filament\Resources\AssetRequestsResource\Pages;

use App\Filament\Resources\AssetRequestsResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetRequests extends CreateRecord
{
    protected static string $resource = AssetRequestsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil!')
            ->body('Data permintaan barang berhasil ditambahkan.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate totals before creating
        if (isset($data['items']) && is_array($data['items'])) {
            $data['total_items'] = count($data['items']);
            $data['total_quantity'] = collect($data['items'])->sum('quantity');
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $items = $this->form->getState()['items'] ?? [];

        if (!empty($items)) {
            foreach ($items as $itemData) {
                $this->record->items()->create([
                    'asset_name' => $itemData['asset_name'],
                    'category_id' => $itemData['category_id'],
                    'quantity' => $itemData['quantity'],
                    'location_id' => $itemData['location_id'],
                    'sub_location_id' => $itemData['sub_location_id'] ?? null,
                    'purpose' => $itemData['purpose'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Update totals after items created
            $this->record->updateTotals();
        }
    }
}
