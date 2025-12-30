<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterTaxType extends Model
{
    use HasFactory;

    protected $table = 'master_tax_types';

    protected $fillable = [
        'name',
        'code',
        'description',
        'asset_category_id',
        'period_type',
        'period_months',
        'has_penalty',
        'penalty_percentage',
        'penalty_type',
        'penalty_period',
        'reminder_days',
        'is_active',
    ];

    protected $casts = [
        'has_penalty' => 'boolean',
        'is_active' => 'boolean',
        'penalty_percentage' => 'decimal:2',
        'period_months' => 'integer',
        'reminder_days' => 'integer',
    ];

    /**
     * Relasi ke kategori aset
     */
    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(MasterAssetsCategory::class, 'asset_category_id');
    }

    /**
     * Relasi ke transaksi pajak
     */
    public function assetTaxes(): HasMany
    {
        return $this->hasMany(AssetTax::class, 'tax_type_id');
    }

    /**
     * Scope untuk pajak aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope berdasarkan kategori aset
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('asset_category_id', $categoryId);
    }

    /**
     * Get periode dalam bulan
     */
    public function getPeriodInMonthsAttribute(): int
    {
        return match($this->period_type) {
            'yearly' => 12,
            '5yearly' => 60,
            'custom' => $this->period_months ?? 12,
            default => 12,
        };
    }

    /**
     * Get label periode
     */
    public function getPeriodLabelAttribute(): string
    {
        return match($this->period_type) {
            'yearly' => 'Tahunan',
            '5yearly' => '5 Tahunan',
            'custom' => ($this->period_months ?? 12) . ' Bulan',
            default => 'Tahunan',
        };
    }
}
