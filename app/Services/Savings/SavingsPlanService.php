<?php

namespace App\Services\Savings;

use App\Models\SavingsCategory;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SavingsPlanService
{
    public function forTeam(Team $team, User $user): ?SavingsPlan
    {
        return SavingsPlan::query()
            ->where('team_id', $team->id)
            ->where(function ($query) use ($user, $team) {
                $query->where('created_by_user_id', $user->id)
                    ->orWhere('is_shared_with_team', true);
            })
            ->with('categories')
            ->first();
    }

    public function cloneFromTemplate(Team $team, User $user, SavingsFormulaTemplate $template, string $name): SavingsPlan
    {
        if ($this->forTeam($team, $user) !== null) {
            throw ValidationException::withMessages([
                'plan' => __('A savings plan already exists for this team.'),
            ]);
        }

        return DB::transaction(function () use ($team, $user, $template, $name) {
            $template->load('categories');

            $plan = SavingsPlan::query()->create([
                'team_id' => $team->id,
                'created_by_user_id' => $user->id,
                'name' => $name,
                'currency' => 'PHP',
                'is_shared_with_team' => false,
            ]);

            foreach ($template->categories as $index => $category) {
                SavingsCategory::query()->create([
                    'plan_id' => $plan->id,
                    'name' => $category->name,
                    'percentage' => $category->percentage,
                    'sort_order' => $index,
                ]);
            }

            return $plan->load('categories');
        });
    }

    /**
     * @param  list<array{name: string, percentage: float|int|string}>  $categories
     */
    public function updateCategories(SavingsPlan $plan, array $categories): SavingsPlan
    {
        if ($plan->hasLockedIncomePeriod()) {
            throw ValidationException::withMessages([
                'categories' => __('Cannot edit categories while a locked income period exists.'),
            ]);
        }

        $total = collect($categories)->sum(fn (array $category) => (float) $category['percentage']);

        if (round($total, 2) !== 100.0) {
            throw ValidationException::withMessages([
                'categories' => __('Category percentages must total exactly 100%.'),
            ]);
        }

        return DB::transaction(function () use ($plan, $categories) {
            $plan->categories()->delete();

            foreach ($categories as $index => $category) {
                SavingsCategory::query()->create([
                    'plan_id' => $plan->id,
                    'name' => $category['name'],
                    'percentage' => $category['percentage'],
                    'sort_order' => $index,
                ]);
            }

            return $plan->fresh('categories');
        });
    }

    public function updateShareSetting(SavingsPlan $plan, bool $isShared): SavingsPlan
    {
        $plan->update(['is_shared_with_team' => $isShared]);

        return $plan->fresh();
    }
}
