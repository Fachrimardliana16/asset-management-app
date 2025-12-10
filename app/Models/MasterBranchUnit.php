<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterBranchUnit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_branch_unit';
    protected $fillable = ['name', 'desc', 'users_id',];

    public $timestamps = true;

    public function BranchOffice()
    {
        return $this->hasMany(MasterBranchOffice::class, 'branch_unit_id');
    }

    public function BranchOfficeUnit()
    {
        return $this->hasMany(MasterBranchOfficeUnits::class, 'branch_unit_id');
    }
}
