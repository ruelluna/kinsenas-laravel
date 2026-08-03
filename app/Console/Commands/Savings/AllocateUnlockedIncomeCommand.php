<?php

namespace App\Console\Commands\Savings;

use App\Models\IncomePeriod;
use App\Models\User;
use App\Services\Savings\IncomeCalculationService;
use Illuminate\Console\Command;

class AllocateUnlockedIncomeCommand extends Command
{
    protected $signature = 'savings:allocate-unlocked-income';

    protected $description = 'Persist fund allocations for income periods that were saved before auto-allocation';

    public function handle(IncomeCalculationService $incomeService): int
    {
        $periods = IncomePeriod::query()
            ->where('is_locked', false)
            ->with('plan.team')
            ->get();

        if ($periods->isEmpty()) {
            $this->info('No unlocked income periods found.');

            return self::SUCCESS;
        }

        $allocated = 0;

        foreach ($periods as $period) {
            $user = $this->resolveUserForPeriod($period);

            if ($user === null) {
                $this->warn("Skipping period {$period->id}: no user found for allocation.");

                continue;
            }

            $incomeService->allocateUnlockedPeriod($period, $user);
            $allocated++;
        }

        $this->info("Allocated {$allocated} income period(s).");

        return self::SUCCESS;
    }

    private function resolveUserForPeriod(IncomePeriod $period): ?User
    {
        if ($period->locked_by_user_id !== null) {
            return User::query()->find($period->locked_by_user_id);
        }

        $period->loadMissing('plan.creator', 'plan.team');

        $owner = $period->plan?->team?->owner();

        if ($owner instanceof User) {
            return $owner;
        }

        return $period->plan?->creator;
    }
}
