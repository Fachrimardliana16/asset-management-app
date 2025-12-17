<?php

namespace App\Filament\Resources\AssetRequestsResource\Pages;

use App\Filament\Resources\AssetRequestsResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAssetRequests extends EditRecord
{
    protected static string $resource = AssetRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Berhasil!')
                        ->body('Data permintaan barang berhasil dihapus.')
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil!')
            ->body('Data permintaan barang berhasil diperbarui.');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load items into form
        $data['items'] = $this->record->items()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'asset_name' => $item->asset_name,
                'category_id' => $item->category_id,
                'quantity' => $item->quantity,
                'location_id' => $item->location_id,
                'sub_location_id' => $item->sub_location_id,
                'purpose' => $item->purpose,
                'notes' => $item->notes,
            ];
        })->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calculate totals before saving
        if (isset($data['items']) && is_array($data['items'])) {
            $data['total_items'] = count($data['items']);
            $data['total_quantity'] = collect($data['items'])->sum('quantity');
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $items = $this->form->getState()['items'] ?? [];

        // Get existing item IDs
        $existingItemIds = collect($items)
            ->filter(fn($item) => isset($item['id']))
            ->pluck('id')
            ->toArray();

        // Delete items that were removed
        $this->record->items()
            ->whereNotIn('id', $existingItemIds)
            ->delete();

        // Update or create items
        foreach ($items as $itemData) {
            if (isset($itemData['id'])) {
                // Update existing item
                $this->record->items()->find($itemData['id'])->update([
                    'asset_name' => $itemData['asset_name'],
                    'category_id' => $itemData['category_id'],
                    'quantity' => $itemData['quantity'],
                    'location_id' => $itemData['location_id'],
                    'sub_location_id' => $itemData['sub_location_id'] ?? null,
                    'purpose' => $itemData['purpose'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            } else {
                // Create new item
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
        }

        // Update totals after items updated
        $this->record->updateTotals();
    }
}
