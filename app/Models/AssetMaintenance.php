<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    use HasFactory, HasUuids;

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
    ];

    public function AssetMaintenance()
    {
        return $this->belongsTo(Asset::class, 'assets_id', 'id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'assets_id');
    }
}
