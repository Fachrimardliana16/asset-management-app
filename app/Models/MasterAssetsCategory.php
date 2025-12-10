<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterAssetsCategory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_assets_category';
    protected $fillable = ['kode', 'name', 'desc', 'users_id'];

    public $timestamps = true;

    public function categoryAsset()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}
