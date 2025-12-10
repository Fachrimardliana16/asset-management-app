<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterAssetsStatus extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_assets_status';

    protected $fillable = [
        'name',
        'desc',
        'users_id',
    ];

    public function AssetsMutationlocation()
    {
        return $this->hasMany(AssetMutation::class, 'location_id');
    }
    public function AssetTransactionStatus()
    {
        return $this->hasMany(Asset::class, 'transaction_status_id', 'id');
    }
}
