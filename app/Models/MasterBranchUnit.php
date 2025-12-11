<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan ini

class MasterBranchUnit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_branch_unit';

    // Tambahkan 'accounting_code' dan 'branch_office_id' ke fillable
    protected $fillable = ['name', 'desc', 'users_id', 'accounting_code', 'branch_office_id'];

    public $timestamps = true;

    // RELATIONSHIP YANG SALAH DIHAPUS: Unit tidak punya HasMany ke Office
    // public function BranchOffice() { return $this->hasMany(MasterBranchOffice::class, 'branch_unit_id'); }

    // TAMBAHKAN RELATIONSHIP YANG BENAR: Unit dimiliki oleh satu Office
    public function branchOffice(): BelongsTo
    {
        return $this->belongsTo(MasterBranchOffice::class, 'branch_office_id');
    }
}
