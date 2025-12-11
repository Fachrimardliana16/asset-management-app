<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetMutation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'assets_mutation';

    protected $fillable = [
        'mutation_date',
        'mutations_number',
        'assets_id',
        'name',
        'assets_number',
        'condition_id',
        'employees_id',
        'location_id',
        'sub_location_id',
        'transaction_status_id',
        'scan_doc',
        'desc',
        'users_id',
    ];

    protected $casts = [
        'mutation_date' => 'date',
    ];

    public function AssetsMutation()
    {
        return $this->belongsTo(Asset::class, 'assets_id', 'id');
    }

    public function AssetsMutationemployee()
    {
        return $this->belongsTo(Employee::class, 'employees_id', 'id');
    }

    public function AssetsMutationlocation()
    {
        return $this->belongsTo(MasterAssetsLocation::class, 'location_id', 'id');
    }

    public function AssetsMutationsubLocation()
    {
        return $this->belongsTo(MasterAssetsSubLocation::class, 'sub_location_id', 'id');
    }

    public function AssetsMutationtransactionStatus()
    {
        return $this->belongsTo(MasterAssetsTransactionStatus::class, 'transaction_status_id', 'id');
    }

    public function transactionStatus()
    {
        return $this->belongsTo(MasterAssetsTransactionStatus::class, 'transaction_status_id', 'id');
    }

    public function MutationCondition()
    {
        return $this->belongsTo(MasterAssetsCondition::class, 'condition_id', 'id');
    }
}
