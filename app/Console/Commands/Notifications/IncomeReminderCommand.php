<?php

namespace App\Console\Commands\Notifications;

use App\Models\IncomePeriod;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Notifications\Savings\IncomeReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class IncomeReminderCommand extends Command
{
    protected $signature = 'notifications:income-reminder';

    protected $description = 'Remind plan owners to log income on or after their payday';

    public function handle(): int
    {
        $notified = 0;
        $today = now()->day;
        $yesterday = now()->subDay()->day;

        User::query()
            ->whereNotNull('payday_day_of_month')
            ->where(function ($query) use ($today, $yesterday) {
                $query->where('payday_day_of_month', $today)
                    ->orWhere('payday_day_of_month', $yesterday);
            })
            ->each(function (User $user) use (&$notified): void {
                $plan = SavingsPlan::query()
                    ->where('created_by_user_id', $user->id)
                    ->whereHas('team', fn ($query) => $query->where('id', $user->current_team_id))
                    ->first();

                if ($plan === null) {
                    $plan = SavingsPlan::query()
                        ->where('created_by_user_id', $user->id)
                        ->latest()
                        ->first();
                }

                if ($plan === null) {
                    return;
                }

                if ($this->hasIncomeThisMonth($plan)) {
                    return;
                }

                if ($this->hasRecentIncomeReminder($user, (string) $plan->id)) {
                    return;
                }

                $user->notify(new IncomeReminder($plan));
                $notified++;
            });

        $this->info("Sent {$notified} income reminder(s).");

        return self::SUCCESS;
    }

    private function hasIncomeThisMonth(SavingsPlan $plan): bool
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        return IncomePeriod::query()
            ->where('plan_id', $plan->id)
            ->whereBetween('period_start', [$monthStart, $monthEnd])
            ->exists();
    }

    private function hasRecentIncomeReminder(User $user, string $planId): bool
    {
        return $user->notifications()
            ->where('data->meta->planId', $planId)
            ->where('data->kind', 'income_reminder')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->exists();
    }
}
