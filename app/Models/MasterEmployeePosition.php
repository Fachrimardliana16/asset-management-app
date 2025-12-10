<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterEmployeePosition extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_employee_position';

    protected $fillable = [
        'name',
        'desc',
        'users_id',
    ];
}
