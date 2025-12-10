<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterAssetsTransactionStatus extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_assets_transaction_status';
    protected $fillable = ['name', 'desc', 'users_id',];

    public $timestamps = true;

    public function AssetsMutationtransactionStatus()
    {
        return $this->hasMany(AssetMutation::class, 'transaction_status_id', 'id');
    }

    public function AssetsTransactionStatus()
    {
        return $this->hasMany(Asset::class, 'transaction_status_id');
    }
}
