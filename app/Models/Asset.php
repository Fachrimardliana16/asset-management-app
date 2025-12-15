<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Asset extends Model
{
    use HasFactory, HasUuids;

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
}
