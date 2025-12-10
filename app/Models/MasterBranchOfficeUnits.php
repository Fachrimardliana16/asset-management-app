<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterBranchOfficeUnits extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_branch_office_units';
    protected $fillable = ['branch_unit_id', 'branch_office_id', 'users_id',];

    public $timestamps = true;

    public function BranchUnitOffice()
    {
        return $this->belongsTo(MasterBranchUnit::class, 'branch_office_id');
    }

    public function BranchOfficeUnit()
    {
        return $this->belongsTo(MasterBranchOffice::class, 'branch_unit_id');
    }
}
