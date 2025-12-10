<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterAssetsSubLocation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_assets_sub_locations';
    protected $fillable = ['name', 'location_id', 'users_id',];

    public $timestamps = true;

    public function Locations()
    {
        return $this->belongsTo(MasterAssetsLocation::class, 'location_id');
    }

    public function monitoringSubLocation()
    {
        return $this->hasMany(AssetMonitoring::class, 'sub_location_id', 'id');
    }

    public function AssetsMutationsubLocation()
    {
        return $this->hasMany(AssetMutation::class, 'sub_location_id');
    }
}
