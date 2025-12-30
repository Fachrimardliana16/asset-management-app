<?php

namespace App\Console\Commands;

use App\Models\AssetTax;
use App\Models\User;
use App\Notifications\TaxReminderNotification;
use App\Services\TaxPenaltyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendTaxReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tax:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send reminders for upcoming tax payments';

    protected TaxPenaltyService $taxPenaltyService;

    public function __construct(TaxPenaltyService $taxPenaltyService)
    {
        parent::__construct();
        $this->taxPenaltyService = $taxPenaltyService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending tax reminders...');

        // Get taxes that need reminders based on their tax type reminder_days
        $taxes = AssetTax::with(['asset', 'taxType', 'asset.user'])
            ->where('payment_status', 'pending')
            ->get()
            ->filter(function ($tax) {
                if (!$tax->taxType) return false;
                
                $reminderDays = $tax->taxType->reminder_days ?? 30;
                $daysUntilDue = now()->diffInDays($tax->due_date, false);
                
                // Send reminder if within reminder window
                return $daysUntilDue >= 0 && $daysUntilDue <= $reminderDays;
            });

        $sentCount = 0;

        foreach ($taxes as $tax) {
            try {
                $daysLeft = now()->diffInDays($tax->due_date, false);
                
                // Send to admin users (users with permission to manage taxes)
                $admins = User::permission('view_any_asset::tax')->get();
                
                foreach ($admins as $admin) {
                    $admin->notify(new TaxReminderNotification($tax, $daysLeft));
                }

                // If asset has assigned user, notify them too
                if ($tax->asset->user) {
                    $tax->asset->user->notify(new TaxReminderNotification($tax, $daysLeft));
                }

                $sentCount++;
                $this->info("Sent reminder for tax #{$tax->id} - {$tax->asset->name} ({$daysLeft} days left)");
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for tax #{$tax->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sentCount} tax reminders successfully.");

        return Command::SUCCESS;
    }
}
