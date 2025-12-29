<?php

namespace App\Console\Commands;

use App\Models\AssetTax;
use App\Models\User;
use App\Notifications\TaxOverdueNotification;
use App\Services\TaxPenaltyService;
use Illuminate\Console\Command;

class UpdateTaxPenalties extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tax:update-penalties';

    /**
     * The console command description.
     */
    protected $description = 'Update penalties for overdue taxes and send notifications';

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
        $this->info('Updating tax penalties...');

        // First, update status for overdue taxes
        $statusUpdated = $this->taxPenaltyService->updateOverdueStatus();
        $this->info("Updated {$statusUpdated} taxes to overdue status.");

        // Then, update penalties
        $penaltiesUpdated = $this->taxPenaltyService->updateOverduePenalties();
        $this->info("Updated penalties for {$penaltiesUpdated} overdue taxes.");

        // Send notifications for newly overdue taxes
        $this->sendOverdueNotifications();

        $this->info('Tax penalty update completed.');

        return Command::SUCCESS;
    }

    protected function sendOverdueNotifications()
    {
        // Get taxes that just became overdue today
        $newlyOverdue = AssetTax::where('payment_status', 'overdue')
            ->whereDate('due_date', '=', now()->subDay()->toDateString())
            ->with(['asset', 'taxType'])
            ->get();

        $sentCount = 0;

        foreach ($newlyOverdue as $tax) {
            try {
                // Send to admin users
                $admins = User::permission('view_any_asset::tax')->get();
                
                foreach ($admins as $admin) {
                    $admin->notify(new TaxOverdueNotification($tax));
                }

                // If asset has assigned user, notify them too
                if ($tax->asset->user) {
                    $tax->asset->user->notify(new TaxOverdueNotification($tax));
                }

                $sentCount++;
            } catch (\Exception $e) {
                $this->error("Failed to send overdue notification for tax #{$tax->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sentCount} overdue notifications.");
    }
}
