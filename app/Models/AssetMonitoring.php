<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetMonitoring extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'assets_monitoring';

    protected $fillable = [
        'monitoring_date',
        'assets_id',
        'name',
        'assets_number',
        'old_condition_id',
        'new_condition_id',
        'user_id',
        'desc',
        'users_id',
    ];

    public function assetMonitoring()
    {
        return $this->belongsTo(Asset::class, 'assets_id', 'id');
    }

    public function employeeAssetMonitoring()
    {
        return $this->belongsTo(Employee::class, 'employees_id');
    }

    public function monitoringLocation()
    {
        return $this->belongsTo(MasterAssetsLocation::class, 'location_id', 'id');
    }

    public function monitoringSubLocation()
    {
        return $this->belongsTo(MasterAssetsSubLocation::class, 'sub_location_id', 'id');
    }

    public function MonitoringoldCondition()
    {
        return $this->belongsTo(MasterAssetsCondition::class, 'old_condition_id', 'id');
    }

    public function MonitoringNewCondition()
    {
        return $this->belongsTo(MasterAssetsCondition::class, 'new_condition_id', 'id');
    }

    public function newCondition()
    {
        return $this->belongsTo(MasterAssetsCondition::class, 'new_condition_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
