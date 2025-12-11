<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterBranchOffice extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_branch_office';

    // Hapus 'branch_unit_id' karena sudah dihapus dari migrasi
    protected $fillable = ['code', 'name', 'address', 'phone', 'users_id'];

    public $timestamps = true;

    public function units(): HasMany
    {
        // Foreign Key ada di tabel master_branch_unit (branch_office_id)
        return $this->hasMany(MasterBranchUnit::class, 'branch_office_id');
    }
}
