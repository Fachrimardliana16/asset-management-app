<?php

namespace App\Notifications;

use App\Models\AssetTax;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaxReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected AssetTax $tax;
    protected int $daysLeft;

    /**
     * Create a new notification instance.
     */
    public function __construct(AssetTax $tax, int $daysLeft)
    {
        $this->tax = $tax;
        $this->daysLeft = $daysLeft;
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
        return [
            'tax_id' => $this->tax->id,
            'asset_id' => $this->tax->asset_id,
            'asset_name' => $this->tax->asset->name,
            'tax_type' => $this->tax->taxType->name,
            'tax_year' => $this->tax->tax_year,
            'tax_amount' => $this->tax->tax_amount,
            'due_date' => $this->tax->due_date->format('Y-m-d'),
            'days_left' => $this->daysLeft,
            'message' => "Pajak {$this->tax->taxType->name} untuk aset {$this->tax->asset->name} akan jatuh tempo dalam {$this->daysLeft} hari",
            'type' => 'tax_reminder',
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
