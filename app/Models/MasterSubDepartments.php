<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterSubDepartments extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_sub_departments';
    protected $fillable = ['name', 'departments_id', 'desc', 'users_id'];

    public $timestamps = true;

    public function Departments()
    {
        return $this->belongsTo(MasterDepartments::class, 'departments_id');
    }

    public function employeesubDepartments()
    {
        return $this->hasMany(Employee::class, 'sub_department_id');
    }

    public function oldSubDepartment()
    {
        return $this->hasMany(EmployeeMutations::class, 'old_sub_department_id', 'id');
    }

    public function newSubDepartment()
    {
        return $this->hasMany(EmployeeMutations::class, 'new_sub_department_id', 'id');
    }
}
