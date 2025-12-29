<?php

namespace App\Services;

use App\Models\AssetTax;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaxOverdueNotification;

class TaxPenaltyService
{
    /**
     * Update penalty untuk pajak yang overdue
     */
    public function updateOverduePenalties(): int
    {
        $overdueTaxes = AssetTax::overdue()->get();
        $updated = 0;

        foreach ($overdueTaxes as $tax) {
            try {
                $tax->updatePenalty();
                $updated++;
            } catch (\Exception $e) {
                Log::error("Failed to update penalty for tax ID {$tax->id}: {$e->getMessage()}");
            }
        }

        Log::info("Updated penalties for {$updated} overdue taxes");
        
        return $updated;
    }

    /**
     * Update penalty untuk semua pajak
     */
    public function updateAllPenalties(): int
    {
        $taxes = AssetTax::whereIn('payment_status', ['pending', 'overdue'])->get();
        $updated = 0;

        foreach ($taxes as $tax) {
            try {
                $tax->updatePenalty();
                $updated++;
            } catch (\Exception $e) {
                Log::error("Failed to update penalty for tax ID {$tax->id}: {$e->getMessage()}");
            }
        }

        Log::info("Updated penalties for {$updated} taxes");
        
        return $updated;
    }

    /**
     * Update status menjadi overdue untuk pajak yang melewati due date
     */
    public function updateOverdueStatus(): int
    {
        $updated = AssetTax::where('payment_status', 'pending')
            ->where('due_date', '<', now())
            ->update([
                'payment_status' => 'overdue',
            ]);

        Log::info("Updated {$updated} taxes to overdue status");
        
        return $updated;
    }

    /**
     * Kalkulasi total denda untuk aset tertentu
     */
    public function calculateTotalPenaltyForAsset(int $assetId): float
    {
        return AssetTax::where('asset_id', $assetId)
            ->overdue()
            ->sum('penalty_amount');
    }

    /**
     * Kalkulasi total pajak yang belum dibayar untuk aset tertentu
     */
    public function calculateUnpaidTaxForAsset(int $assetId): float
    {
        return AssetTax::where('asset_id', $assetId)
            ->unpaid()
            ->sum('tax_amount');
    }

    /**
     * Get laporan denda per periode
     */
    public function getPenaltyReport(string $startDate, string $endDate): array
    {
        $taxes = AssetTax::whereBetween('due_date', [$startDate, $endDate])
            ->overdue()
            ->with(['asset', 'taxType'])
            ->get();

        $totalPenalty = $taxes->sum('penalty_amount');
        $totalTax = $taxes->sum('tax_amount');
        $totalAmount = $taxes->sum('total_amount');

        return [
            'taxes' => $taxes,
            'summary' => [
                'total_taxes' => $taxes->count(),
                'total_tax_amount' => $totalTax,
                'total_penalty' => $totalPenalty,
                'total_amount' => $totalAmount,
            ]
        ];
    }

    /**
     * Proses pembayaran pajak
     */
    public function processTaxPayment(AssetTax $tax, array $data): bool
    {
        try {
            // Update penalty terakhir kali sebelum payment
            $tax->updatePenalty();

            $tax->update([
                'payment_date' => $data['payment_date'] ?? now(),
                'payment_status' => 'paid',
                'notes' => $data['notes'] ?? null,
                'paid_by' => auth()->id(),
            ]);

            Log::info("Tax payment processed for tax ID {$tax->id}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to process tax payment for tax ID {$tax->id}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Generate pajak otomatis untuk tahun berikutnya
     */
    public function generateNextYearTax(AssetTax $currentTax): ?AssetTax
    {
        try {
            $taxType = $currentTax->taxType;
            
            if (!$taxType) {
                return null;
            }

            $nextYear = $currentTax->tax_year + 1;
            $nextDueDate = $currentTax->due_date->addYear();

            // Check if next year tax already exists
            $exists = AssetTax::where('asset_id', $currentTax->asset_id)
                ->where('tax_type_id', $currentTax->tax_type_id)
                ->where('tax_year', $nextYear)
                ->exists();

            if ($exists) {
                Log::info("Tax for next year already exists for asset {$currentTax->asset_id}");
                return null;
            }

            $nextTax = AssetTax::create([
                'asset_id' => $currentTax->asset_id,
                'tax_type_id' => $currentTax->tax_type_id,
                'tax_year' => $nextYear,
                'tax_amount' => $currentTax->tax_amount, // Could be adjusted
                'due_date' => $nextDueDate,
                'payment_status' => 'pending',
                'approval_status' => 'pending',
            ]);

            Log::info("Generated next year tax for asset {$currentTax->asset_id}, year {$nextYear}");
            
            return $nextTax;
        } catch (\Exception $e) {
            Log::error("Failed to generate next year tax: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Get statistik denda
     */
    public function getPenaltyStatistics(): array
    {
        $totalOverdue = AssetTax::overdue()->count();
        $totalPenalty = AssetTax::overdue()->sum('penalty_amount');
        $avgPenalty = $totalOverdue > 0 ? $totalPenalty / $totalOverdue : 0;
        $maxPenalty = AssetTax::overdue()->max('penalty_amount');
        
        $penaltyByType = AssetTax::overdue()
            ->with('taxType')
            ->get()
            ->groupBy('tax_type_id')
            ->map(function ($taxes) {
                return [
                    'type' => $taxes->first()->taxType->name,
                    'count' => $taxes->count(),
                    'total_penalty' => $taxes->sum('penalty_amount'),
                ];
            });

        return [
            'total_overdue' => $totalOverdue,
            'total_penalty' => $totalPenalty,
            'average_penalty' => $avgPenalty,
            'max_penalty' => $maxPenalty,
            'by_type' => $penaltyByType,
        ];
    }
}
