<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterCabangUnit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_cabang_unit';

    protected $fillable = ['kode', 'nama', 'alamat', 'keterangan', 'users_id'];

    public $timestamps = true;
}
