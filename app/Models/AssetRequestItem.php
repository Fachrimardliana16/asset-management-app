<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetRequestItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'asset_request_items';

    protected $fillable = [
        'asset_request_id',
        'asset_name',
        'category_id',
        'location_id',
        'sub_location_id',
        'quantity',
        'purpose',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Relasi ke Asset Request (Master)
     */
    public function assetRequest()
    {
        return $this->belongsTo(AssetRequests::class, 'asset_request_id');
    }

    /**
     * Relasi ke Kategori
     */
    public function category()
    {
        return $this->belongsTo(MasterAssetsCategory::class, 'category_id');
    }

    /**
     * Relasi ke Lokasi
     */
    public function location()
    {
        return $this->belongsTo(MasterAssetsLocation::class, 'location_id');
    }

    /**
     * Relasi ke Sub Lokasi
     */
    public function subLocation()
    {
        return $this->belongsTo(MasterAssetsSubLocation::class, 'sub_location_id');
    }

    /**
     * Relasi ke Purchase (hasMany karena 1 item bisa dibeli dalam beberapa batch/unit)
     */
    public function purchases()
    {
        return $this->hasMany(AssetPurchase::class, 'asset_request_item_id');
    }

    /**
     * Helper untuk cek apakah item sudah dibeli semua
     */
    public function isPurchasedComplete(): bool
    {
        $purchasedCount = $this->purchases()->count();
        return $purchasedCount >= $this->quantity;
    }

    /**
     * Helper untuk jumlah yang sudah dibeli
     */
    public function getPurchasedCountAttribute(): int
    {
        return $this->purchases()->count();
    }

    /**
     * Helper untuk jumlah yang belum dibeli
     */
    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->purchased_count);
    }

    /**
     * Helper untuk status pembelian item
     */
    public function getPurchaseStatusAttribute(): string
    {
        if ($this->purchased_count === 0) {
            return 'pending';
        } elseif ($this->purchased_count < $this->quantity) {
            return 'partial';
        } else {
            return 'complete';
        }
    }

    /**
     * Helper untuk label status
     */
    public function getPurchaseStatusLabelAttribute(): string
    {
        return match ($this->purchase_status) {
            'pending' => 'Belum Dibeli',
            'partial' => 'Dibeli Sebagian',
            'complete' => 'Sudah Lengkap',
            default => 'Belum Dibeli',
        };
    }

    /**
     * Helper untuk warna badge status
     */
    public function getPurchaseStatusColorAttribute(): string
    {
        return match ($this->purchase_status) {
            'pending' => 'warning',
            'partial' => 'info',
            'complete' => 'success',
            default => 'gray',
        };
    }
}
