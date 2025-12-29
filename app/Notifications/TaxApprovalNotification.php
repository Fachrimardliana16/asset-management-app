<?php

namespace App\Notifications;

use App\Models\AssetTax;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaxApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected AssetTax $tax;
    protected string $action; // 'pending', 'approved', 'rejected'

    /**
     * Create a new notification instance.
     */
    public function __construct(AssetTax $tax, string $action)
    {
        $this->tax = $tax;
        $this->action = $action;
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
        $message = match($this->action) {
            'pending' => "Pajak {$this->tax->taxType->name} untuk aset {$this->tax->asset->name} menunggu approval Anda",
            'approved' => "Pajak {$this->tax->taxType->name} untuk aset {$this->tax->asset->name} telah disetujui",
            'rejected' => "Pajak {$this->tax->taxType->name} untuk aset {$this->tax->asset->name} ditolak. Alasan: {$this->tax->rejection_reason}",
            default => "Update status pajak",
        };

        return [
            'tax_id' => $this->tax->id,
            'asset_id' => $this->tax->asset_id,
            'asset_name' => $this->tax->asset->name,
            'tax_type' => $this->tax->taxType->name,
            'tax_year' => $this->tax->tax_year,
            'tax_amount' => $this->tax->tax_amount,
            'action' => $this->action,
            'approval_status' => $this->tax->approval_status,
            'approved_by' => $this->tax->approvedByUser?->name,
            'rejection_reason' => $this->tax->rejection_reason,
            'message' => $message,
            'type' => 'tax_approval',
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
