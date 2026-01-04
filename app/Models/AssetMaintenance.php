<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetMaintenance extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'assets_maintenance';

    protected $fillable = [
        'maintenance_date',
        'location_service',
        'assets_id',
        'service_type',
        'service_cost',
        'desc',
        'invoice_file',
        'users_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function AssetMaintenance()
    {
        return $this->belongsTo(Asset::class, 'assets_id', 'id');
    }
}
