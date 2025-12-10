<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetDisposal extends Model
{
    use HasFactory, HasUuids;

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
        'disposal_notes',
        'docs',
        'users_id',
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
}
