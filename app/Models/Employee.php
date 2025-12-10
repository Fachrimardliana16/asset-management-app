<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employees';

    protected $fillable = [
        'nippam',
        'name',
        'departments_id',
        'sub_department_id',
        'employee_position_id',
        'place_birth',
        'date_birth',
        'gender',
        'religion',
        'age',
        'address',
        'blood_type',
        'marital_status',
        'phone_number',
        'id_number',
        'email',
        'users_id',
    ];

    /**
     * Get the department (Bagian)
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(MasterDepartments::class, 'departments_id');
    }

    /**
     * Get the sub department (Sub Bagian)
     */
    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterSubDepartments::class, 'sub_department_id');
    }

    /**
     * Get the position (Jabatan)
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeePosition::class, 'employee_position_id');
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
