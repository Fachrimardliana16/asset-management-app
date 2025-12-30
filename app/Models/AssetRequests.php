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
        'total_items',
        'total_quantity',
        'department_id',
        'requested_by',
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
        'total_items' => 'integer',
        'total_quantity' => 'integer',
    ];

    /**
     * Relasi ke Items (Detail)
     */
    public function items()
    {
        return $this->hasMany(AssetRequestItem::class, 'asset_request_id');
    }

    /**
     * Relasi ke Department
     */
    public function department()
    {
        return $this->belongsTo(MasterDepartments::class, 'department_id');
    }

    /**
     * Relasi ke Employee Pemohon
     */
    public function requestedBy()
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    /**
     * Relasi ke User Pembuat
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    /**
     * Relasi ke Purchases (melalui items)
     */
    public function purchases()
    {
        return $this->hasManyThrough(
            AssetPurchase::class,
            AssetRequestItem::class,
            'asset_request_id', // FK di asset_request_items
            'asset_request_item_id', // FK di asset_purchases
            'id', // PK di assets_requests
            'id' // PK di asset_request_items
        );
    }

    /**
     * Helper untuk mendapatkan label status
     */
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

    /**
     * Helper untuk mendapatkan warna badge
     */
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

    /**
     * Helper untuk menghitung total items setelah update
     */
    public function updateTotals(): void
    {
        $this->update([
            'total_items' => $this->items()->count(),
            'total_quantity' => $this->items()->sum('quantity'),
        ]);
    }

    /**
     * Helper untuk cek apakah semua item sudah dibeli lengkap
     */
    public function isAllItemsPurchased(): bool
    {
        $totalItems = $this->items()->count();
        if ($totalItems === 0) {
            return false;
        }

        $completedItems = $this->items()->get()->filter(function ($item) {
            return $item->isPurchasedComplete();
        })->count();

        return $completedItems === $totalItems;
    }

    /**
     * Helper untuk progress pembelian (%)
     */
    public function getPurchaseProgressAttribute(): int
    {
        $totalQuantity = $this->total_quantity;
        if ($totalQuantity === 0) {
            return 0;
        }

        $purchasedQuantity = $this->purchases()->count();
        return (int) min(100, ($purchasedQuantity / $totalQuantity) * 100);
    }
}

