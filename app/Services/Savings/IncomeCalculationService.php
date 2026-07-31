<?php

namespace App\Services\Savings;

use App\Models\IncomeAllocation;
use App\Models\IncomePeriod;
use App\Models\SavingsPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IncomeCalculationService
{
    public function create(SavingsPlan $plan, string $amount, string $periodStart): IncomePeriod
    {
        $period = IncomePeriod::query()->create([
            'plan_id' => $plan->id,
            'amount_encrypted' => $amount,
            'period_start' => $periodStart,
        ]);

        return $period->load('plan.categories');
    }

    /**
     * @return array<int, array{category_id: string, name: string, percentage: string, amount: string}>
     */
    public function preview(SavingsPlan $plan, string $amount): array
    {
        $plan->loadMissing('categories');

        return $plan->categories->map(function ($category) use ($amount) {
            $computed = bcmul($amount, bcdiv((string) $category->percentage, '100', 6), 2);

            return [
                'category_id' => $category->id,
                'name' => $category->name,
                'percentage' => (string) $category->percentage,
                'amount' => $computed,
            ];
        })->all();
    }

    public function lock(IncomePeriod $period, User $user): IncomePeriod
    {
        if ($period->is_locked) {
            return $period->load('allocations.category');
        }

        return DB::transaction(function () use ($period, $user) {
            $period->load('plan.categories');
            $amount = $period->amount_encrypted;

            if ($amount === null) {
                throw ValidationException::withMessages([
                    'amount' => __('Income amount is required before locking.'),
                ]);
            }

            foreach ($period->plan->categories as $category) {
                IncomeAllocation::query()->create([
                    'income_period_id' => $period->id,
                    'category_id' => $category->id,
                    'amount_encrypted' => bcmul($amount, bcdiv((string) $category->percentage, '100', 6), 2),
                ]);
            }

            $period->update([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by_user_id' => $user->id,
            ]);

            return $period->fresh(['allocations.category', 'plan.categories']);
        });
    }

    public function unlock(IncomePeriod $period): IncomePeriod
    {
        if ($period->transfers()->where('status', 'confirmed')->exists()) {
            throw ValidationException::withMessages([
                'period' => __('Cannot unlock income with confirmed transfers.'),
            ]);
        }

        return DB::transaction(function () use ($period) {
            $period->transfers()->delete();
            $period->allocations()->delete();

            $period->update([
                'is_locked' => false,
                'locked_at' => null,
                'locked_by_user_id' => null,
            ]);

            return $period->fresh(['plan.categories']);
        });
    }
}
