<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\MediaLibrary\MediaCollections\Models\Concerns\HasUuid;

class MasterAssetsLocation extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'master_assets_locations';
    protected $fillable = ['kode', 'name', 'desc', 'users_id',];

    public $timestamps = true;

    public function Locations()
    {
        return $this->hasMany(MasterAssetsSubLocation::class, 'location_id');
    }

    public function monitoringLocation()
    {
        return $this->hasMany(AssetMonitoring::class, 'location_id', 'id');
    }

    public function AssetsMutationlocation()
    {
        return $this->hasMany(AssetMutation::class, 'location_id');
    }
}
