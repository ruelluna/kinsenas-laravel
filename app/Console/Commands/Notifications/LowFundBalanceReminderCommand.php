<?php

namespace App\Console\Commands\Notifications;

use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Notifications\Savings\LowFundBalance;
use App\Services\Savings\FundBalanceService;
use Illuminate\Console\Command;

class LowFundBalanceReminderCommand extends Command
{
    protected $signature = 'notifications:low-fund-balance';

    protected $description = 'Notify team owners when fund buckets are at least 90% used';

    public function handle(FundBalanceService $balanceService): int
    {
        $notified = 0;

        SavingsPlan::query()
            ->with('team')
            ->each(function (SavingsPlan $plan) use ($balanceService, &$notified): void {
                if ($plan->team === null || ! $plan->canDrawFromFunds()) {
                    return;
                }

                $owner = $plan->team->owner();

                if (! $owner instanceof User) {
                    return;
                }

                foreach ($balanceService->balancesWithDefaultFirst($plan) as $balance) {
                    $percentUsed = $balance['percentUsed'] ?? null;

                    if ($percentUsed === null || $percentUsed < 90) {
                        continue;
                    }

                    $category = SavingsCategory::query()->find($balance['categoryId']);

                    if ($category === null) {
                        continue;
                    }

                    if ($this->hasUnreadLowBalanceNotification($owner, (string) $category->id)) {
                        continue;
                    }

                    $owner->notify(new LowFundBalance($category, (int) $percentUsed));
                    $notified++;
                }
            });

        $this->info("Sent {$notified} low fund balance notification(s).");

        return self::SUCCESS;
    }

    private function hasUnreadLowBalanceNotification(User $user, string $categoryId): bool
    {
        return $user->unreadNotifications()
            ->where('data->meta->categoryId', $categoryId)
            ->exists();
    }
}
