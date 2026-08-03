<?php

namespace App\Console\Commands\Notifications;

use App\Enums\TransferStatus;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Services\Notifications\PendingActionNotificationService;
use Illuminate\Console\Command;

class PendingActionsReminderCommand extends Command
{
    protected $signature = 'notifications:pending-actions-reminder';

    protected $description = 'Remind team members about pending spends and transfers older than 24 hours';

    public function handle(PendingActionNotificationService $notificationService): int
    {
        $cutoff = now()->subDay();
        $notified = 0;

        FundSpend::query()
            ->where('status', TransferStatus::Pending)
            ->whereNotNull('bank_id')
            ->where('created_at', '<=', $cutoff)
            ->with('plan.team')
            ->each(function (FundSpend $spend) use ($notificationService, &$notified): void {
                $notificationService->notifyForSpend($spend);
                $notified++;
            });

        FundTransfer::query()
            ->where('status', TransferStatus::Pending)
            ->where('created_at', '<=', $cutoff)
            ->with('plan.team')
            ->each(function (FundTransfer $transfer) use ($notificationService, &$notified): void {
                $notificationService->notifyForTransfer($transfer);
                $notified++;
            });

        $this->info("Processed {$notified} pending action reminder(s).");

        return self::SUCCESS;
    }
}
