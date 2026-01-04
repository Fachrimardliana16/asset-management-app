<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Asset extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    protected $table = 'assets';

    protected $fillable = [
        'assets_number',
        'name',
        'category_id',
        'purchase_date',
        'condition_id',
        'img',
        'price',
        'funding_source',
        'brand',
        'book_value',
        'book_value_expiry',
        'status_id',
        'transaction_status_id',
        'desc',
        'users_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'book_value_expiry' => 'date',
    ];

    public function categoryAsset()
    {
        return $this->belongsTo(MasterAssetsCategory::class, 'category_id');
    }

    public function conditionAsset()
    {
        return $this->belongsTo(MasterAssetsCondition::class, 'condition_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['assets_number', 'name', 'category_id', 'purchase_date', 'condition_id', 'price', 'status_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function condition()
    {
        return $this->belongsTo(MasterAssetsCondition::class, 'condition_id');
    }

    public function AssetMaintenance()
    {
        return $this->hasMany(AssetMaintenance::class, 'assets_id', 'id');
    }

    public function assetMonitoring()
    {
        return $this->hasMany(AssetMonitoring::class, 'assets_id', 'id');
    }

    public function AssetsMutation()
    {
        return $this->hasMany(AssetMutation::class, 'assets_id');
    }

    /**
     * Get the latest mutation for this asset
     */
    public function latestMutation()
    {
        return $this->hasOne(AssetMutation::class, 'assets_id')->latestOfMany();
    }

    public function assetDisposals()
    {
        return $this->hasMany(AssetDisposal::class, 'assets_id', 'id');
    }

    public function assetsStatus()
    {
        return $this->belongsTo(MasterAssetsStatus::class, 'status_id', 'id');
    }

    public function AssetTransactionStatus()
    {
        return $this->belongsTo(MasterAssetsTransactionStatus::class, 'transaction_status_id', 'id');
    }

    /**
     * Get all taxes for this asset
     */
    public function taxes()
    {
        return $this->hasMany(AssetTax::class, 'asset_id', 'id');
    }

    /**
     * Get active/unpaid taxes for this asset
     */
    public function activeTaxes()
    {
        return $this->hasMany(AssetTax::class, 'asset_id', 'id')
            ->whereIn('payment_status', ['pending', 'overdue']);
    }

    /**
     * Get paid taxes for this asset
     */
    public function paidTaxes()
    {
        return $this->hasMany(AssetTax::class, 'asset_id', 'id')
            ->where('payment_status', 'paid');
    }

    /**
     * Get latest tax for this asset
     */
    public function latestTax()
    {
        return $this->hasOne(AssetTax::class, 'asset_id', 'id')
            ->latestOfMany();
    }
    /**
     * User Tracking Relations
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }}
