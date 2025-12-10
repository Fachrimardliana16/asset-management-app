<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterAssetsCondition extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_assets_condition';
    protected $fillable = ['name', 'desc', 'users_id',];

    public $timestamps = true;

    public function conditionAsset()
    {
        return $this->hasMany(Asset::class, 'condition_id');
    }

    public function MutationCondition()
    {
        return $this->hasMany(AssetMutation::class, 'condition_id');
    }

    public function MonitoringoldCondition()
    {
        return $this->hasMany(AssetMonitoring::class, 'old_condition_id', 'id');
    }

    public function MonitoringNewCondition()
    {
        return $this->hasMany(AssetMonitoring::class, 'new_condition_id', 'id');
    }
}
