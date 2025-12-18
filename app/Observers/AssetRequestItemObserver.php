<?php

namespace App\Observers;

use App\Models\AssetRequestItem;

class AssetRequestItemObserver
{
    /**
     * Handle the AssetRequestItem "created" event.
     */
    public function created(AssetRequestItem $assetRequestItem): void
    {
        $this->updateRequestTotals($assetRequestItem);
    }

    /**
     * Handle the AssetRequestItem "updated" event.
     */
    public function updated(AssetRequestItem $assetRequestItem): void
    {
        $this->updateRequestTotals($assetRequestItem);
    }

    /**
     * Handle the AssetRequestItem "deleted" event.
     */
    public function deleted(AssetRequestItem $assetRequestItem): void
    {
        $this->updateRequestTotals($assetRequestItem);
    }

    /**
     * Update parent request totals
     */
    protected function updateRequestTotals(AssetRequestItem $item): void
    {
        if ($request = $item->assetRequest) {
            $request->updateTotals();
        }
    }
}
