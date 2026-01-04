<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetDisposal extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'assets_disposals';

    protected $fillable = [
        'disposal_date',
        'disposals_number',
        'assets_id',
        'book_value',
        'disposal_reason',
        'disposal_value',
        'disposal_process',
        'employee_id',
        'petugas_id',
        'kepala_sub_bagian_id',
        'direktur_id',
        'disposal_notes',
        'docs',
        'users_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function assetDisposals()
    {
        return $this->belongsTo(Asset::class, 'assets_id', 'id');
    }

    /**
     * Get the employee who handled the disposal.
     */
    public function employeeDisposals()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get the petugas (staff) who created the disposal.
     */
    public function petugas()
    {
        return $this->belongsTo(Employee::class, 'petugas_id', 'id');
    }

    /**
     * Get the head of sub-department who approved the disposal.
     */
    public function kepalaSubBagian()
    {
        return $this->belongsTo(Employee::class, 'kepala_sub_bagian_id', 'id');
    }

    /**
     * Get the director who acknowledged the disposal.
     */
    public function direktur()
    {
        return $this->belongsTo(Employee::class, 'direktur_id', 'id');
    }

    /**
     * Get the user who created the disposal.
     */
    public function userDisposals()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
}
