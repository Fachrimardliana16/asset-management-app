<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetPurchase extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'asset_purchases';

    protected $fillable = [
        'assetrequest_id',
        'document_number',
        'assets_number',
        'asset_name',
        'category_id',
        'employee_id',
        'location_id',
        'sub_location_id',
        'purchase_date',
        'condition_id',
        'status_id',
        'img',
        'price',
        'book_value',
        'book_value_expiry',
        'funding_source',
        'purchase_notes',
        'brand',
        'item_index',
        'users_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function assetRequest()
    {
        return $this->belongsTo(AssetRequests::class, 'assetrequest_id');
    }

    public function category()
    {
        return $this->belongsTo(MasterAssetsCategory::class, 'category_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function location()
    {
        return $this->belongsTo(MasterAssetsLocation::class, 'location_id');
    }

    public function subLocation()
    {
        return $this->belongsTo(MasterAssetsSubLocation::class, 'sub_location_id');
    }

    public function condition()
    {
        return $this->belongsTo(MasterAssetsCondition::class, 'condition_id');
    }

    public function status()
    {
        return $this->belongsTo(MasterAssetsStatus::class, 'status_id');
    }
}
