<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * UserTrackingObserver
 * 
 * Observer untuk otomatis mengisi kolom created_by, updated_by, deleted_by
 * berdasarkan user yang sedang login
 */
class UserTrackingObserver
{
    /**
     * Handle the Model "creating" event.
     */
    public function creating(Model $model): void
    {
        if (Auth::check()) {
            // Set created_by saat data baru dibuat
            if ($model->isFillable('created_by') && is_null($model->created_by)) {
                $model->created_by = Auth::id();
            }
            
            // Set updated_by juga saat create
            if ($model->isFillable('updated_by')) {
                $model->updated_by = Auth::id();
            }
        }
    }

    /**
     * Handle the Model "updating" event.
     */
    public function updating(Model $model): void
    {
        if (Auth::check()) {
            // Set updated_by setiap kali data diupdate
            if ($model->isFillable('updated_by')) {
                $model->updated_by = Auth::id();
            }
        }
    }

    /**
     * Handle the Model "deleting" event.
     */
    public function deleting(Model $model): void
    {
        if (Auth::check()) {
            // Set deleted_by saat data dihapus (soft delete)
            if ($model->isFillable('deleted_by') && method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                $model->deleted_by = Auth::id();
                $model->saveQuietly(); // Save without triggering events
            }
        }
    }

    /**
     * Handle the Model "restoring" event.
     */
    public function restoring(Model $model): void
    {
        if (Auth::check()) {
            // Clear deleted_by saat data direstore
            if ($model->isFillable('deleted_by')) {
                $model->deleted_by = null;
            }
            
            // Set updated_by saat restore
            if ($model->isFillable('updated_by')) {
                $model->updated_by = Auth::id();
            }
        }
    }
}
