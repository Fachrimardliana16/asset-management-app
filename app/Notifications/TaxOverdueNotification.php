<?php

namespace App\Notifications;

use App\Models\AssetTax;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaxOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected AssetTax $tax;

    /**
     * Create a new notification instance.
     */
    public function __construct(AssetTax $tax)
    {
        $this->tax = $tax;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $overdueDays = $this->tax->getOverdueDaysCount();
        
        return [
            'tax_id' => $this->tax->id,
            'asset_id' => $this->tax->asset_id,
            'asset_name' => $this->tax->asset->name,
            'tax_type' => $this->tax->taxType->name,
            'tax_year' => $this->tax->tax_year,
            'tax_amount' => $this->tax->tax_amount,
            'penalty_amount' => $this->tax->penalty_amount,
            'total_amount' => $this->tax->total_amount,
            'due_date' => $this->tax->due_date->format('Y-m-d'),
            'overdue_days' => $overdueDays,
            'message' => "Pajak {$this->tax->taxType->name} untuk aset {$this->tax->asset->name} sudah terlambat {$overdueDays} hari. Denda: Rp " . number_format($this->tax->penalty_amount, 0, ',', '.'),
            'type' => 'tax_overdue',
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
