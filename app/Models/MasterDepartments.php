<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterDepartments extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_departments';
    protected $fillable = ['name', 'desc', 'users_id'];

    public $timestamps = true;

    public function Departments()
    {
        return $this->hasMany(MasterSubDepartments::class, 'departments_id');
    }

    public function employeeDepartments()
    {
        return $this->hasMany(Employee::class, 'departments_id');
    }

    public function oldDepartment()
    {
        return $this->hasMany(EmployeeMutations::class, 'old_department_id', 'id');
    }
    public function newDepartment()
    {
        return $this->hasMany(EmployeeMutations::class, 'new_department_id', 'id');
    }
}
