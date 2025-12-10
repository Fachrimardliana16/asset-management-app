<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterBranchOffice extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_branch_office';
    protected $fillable = ['branch_unit_id', 'code', 'name', 'address', 'phone', 'users_id',];

    public $timestamps = true;

    public function BranchOffice()
    {
        return $this->belongsTo(MasterBranchUnit::class, 'branch_unit_id');
    }

    public function BranchUnitOffice()
    {
        return $this->hasMany(MasterBranchOfficeUnits::class, 'branch_office_id');
    }
}
