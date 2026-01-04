<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AssetTax extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, LogsActivity;

    protected $table = 'asset_taxes';

    protected $fillable = [
        'asset_id',
        'tax_type_id',
        'tax_year',
        'tax_amount',
        'due_date',
        'payment_date',
        'payment_status',
        'approval_status',
        'penalty_amount',
        'penalty_calculation',
        'overdue_days',
        'notes',
        'rejection_reason',
        'paid_by',
        'approved_by',
        'approved_at',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tax_year' => 'integer',
        'tax_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'verified_at' => 'datetime',
        'overdue_days' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['asset_id', 'tax_type_id', 'tax_year', 'tax_amount', 'due_date', 'payment_date', 'payment_status', 'approval_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('payment_proofs')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf'])
            ->maxFilesize(5 * 1024 * 1024); // 5MB
    }

    /**
     * Relasi ke aset
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Relasi ke tipe pajak
     */
    public function taxType(): BelongsTo
    {
        return $this->belongsTo(MasterTaxType::class, 'tax_type_id');
    }

    /**
     * Relasi ke user yang membayar
     */
    public function paidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Relasi ke user yang approve
     */
    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi ke user yang verifikasi
     */
    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope untuk pajak yang sudah dibayar
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope untuk pajak yang belum dibayar
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status', ['pending', 'overdue']);
    }

    /**
     * Scope untuk pajak yang overdue
     */
    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'overdue')
            ->orWhere(function($q) {
                $q->where('payment_status', 'pending')
                  ->where('due_date', '<', now());
            });
    }

    /**
     * Scope untuk pajak yang menunggu approval
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope untuk pajak yang sudah diapprove
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope untuk pajak yang akan jatuh tempo
     */
    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('payment_status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays($days)]);
    }

    /**
     * Check apakah pajak sudah lewat jatuh tempo
     */
    public function isOverdue(): bool
    {
        if ($this->payment_status === 'paid') {
            return false;
        }
        
        return $this->due_date < now();
    }

    /**
     * Hitung jumlah hari keterlambatan
     */
    public function getOverdueDaysCount(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return now()->diffInDays($this->due_date);
    }

    /**
     * Hitung denda otomatis
     */
    public function calculatePenalty(): array
    {
        $taxType = $this->taxType;
        
        if (!$taxType || !$taxType->has_penalty) {
            return [
                'penalty_amount' => 0,
                'overdue_days' => 0,
                'calculation' => 'Tidak ada denda untuk jenis pajak ini'
            ];
        }

        $overdueDays = $this->getOverdueDaysCount();
        
        if ($overdueDays <= 0) {
            return [
                'penalty_amount' => 0,
                'overdue_days' => 0,
                'calculation' => 'Belum melewati jatuh tempo'
            ];
        }

        $penaltyAmount = 0;
        $calculation = '';

        if ($taxType->penalty_type === 'percentage') {
            $penaltyRate = $taxType->penalty_percentage;
            
            if ($taxType->penalty_period === 'daily') {
                // Denda per hari
                $penaltyAmount = $this->tax_amount * ($penaltyRate / 100) * $overdueDays;
                $calculation = sprintf(
                    'Rp %s × %s%% × %d hari = Rp %s',
                    number_format($this->tax_amount, 0, ',', '.'),
                    $penaltyRate,
                    $overdueDays,
                    number_format($penaltyAmount, 0, ',', '.')
                );
            } else {
                // Denda per bulan
                $overdueMonths = ceil($overdueDays / 30);
                $penaltyAmount = $this->tax_amount * ($penaltyRate / 100) * $overdueMonths;
                $calculation = sprintf(
                    'Rp %s × %s%% × %d bulan = Rp %s',
                    number_format($this->tax_amount, 0, ',', '.'),
                    $penaltyRate,
                    $overdueMonths,
                    number_format($penaltyAmount, 0, ',', '.')
                );
            }
        } else {
            // Fixed penalty
            $penaltyAmount = $taxType->penalty_percentage; // Menggunakan field yang sama untuk fixed amount
            $calculation = sprintf('Denda tetap: Rp %s', number_format($penaltyAmount, 0, ',', '.'));
        }

        return [
            'penalty_amount' => round($penaltyAmount, 2),
            'overdue_days' => $overdueDays,
            'calculation' => $calculation
        ];
    }

    /**
     * Update penalty otomatis
     */
    public function updatePenalty(): void
    {
        $penalty = $this->calculatePenalty();
        
        $this->update([
            'penalty_amount' => $penalty['penalty_amount'],
            'overdue_days' => $penalty['overdue_days'],
            'penalty_calculation' => $penalty['calculation'],
        ]);
    }

    /**
     * Get total amount (pajak + denda)
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->tax_amount + $this->penalty_amount;
    }

    /**
     * Get status badge color
     */
    public function getPaymentStatusColorAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'success',
            'pending' => 'warning',
            'overdue' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get approval status badge color
     */
    public function getApprovalStatusColorAttribute(): string
    {
        return match($this->approval_status) {
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Auto update status menjadi overdue jika melewati due_date
        static::saving(function ($model) {
            if ($model->payment_status === 'pending' && $model->due_date < now()) {
                $model->payment_status = 'overdue';
            }
        });
    }
}
