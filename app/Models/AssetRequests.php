<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetRequests extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'assets_requests';

    protected $fillable = [
        'document_number',
        'date',
        'asset_name',
        'category_id',
        'employee_id',
        'location_id',
        'sub_location_id',
        'quantity',
        'purpose',
        'desc',
        'kepala_sub_bagian',
        'kepala_bagian_umum',
        'kepala_bagian_keuangan',
        'direktur_umum',
        'direktur_utama',
        'docs',
        'users_id',
        'status_request',
        'purchase_status',
        'purchase_date',
        'purchase_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'purchase_date' => 'date',
        'kepala_sub_bagian' => 'boolean',
        'kepala_bagian_umum' => 'boolean',
        'kepala_bagian_keuangan' => 'boolean',
        'direktur_umum' => 'boolean',
        'direktur_utama' => 'boolean',
        'status_request' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(MasterAssetsCategory::class, 'category_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function location()
    {
        return $this->belongsTo(MasterAssetsLocation::class, 'location_id');
    }

    public function subLocation()
    {
        return $this->belongsTo(MasterAssetsSubLocation::class, 'sub_location_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function purchases()
    {
        return $this->hasMany(AssetPurchase::class, 'assetrequest_id');
    }

    // Helper untuk mendapatkan label status
    public function getPurchaseStatusLabelAttribute(): string
    {
        return match ($this->purchase_status) {
            'pending' => 'Menunggu',
            'in_progress' => 'Sedang Diproses',
            'purchased' => 'Sudah Dibeli',
            'cancelled' => 'Dibatalkan',
            default => 'Menunggu',
        };
    }

    // Helper untuk mendapatkan warna badge
    public function getPurchaseStatusColorAttribute(): string
    {
        return match ($this->purchase_status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'purchased' => 'success',
            'cancelled' => 'danger',
            default => 'warning',
        };
    }
}
