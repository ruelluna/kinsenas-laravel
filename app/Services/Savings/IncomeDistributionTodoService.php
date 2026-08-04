<?php

namespace App\Services\Savings;

use App\Enums\IncomeDistributionTodoStatus;
use App\Models\IncomeDistributionTodo;
use App\Models\IncomePeriod;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class IncomeDistributionTodoService
{
    public function syncFromPeriod(IncomePeriod $period): void
    {
        $period->loadMissing(['allocations.category.bank.institution']);

        $activeCategoryIds = [];

        foreach ($period->allocations as $allocation) {
            $amount = $allocation->amount_encrypted ?? '0.00';

            if (bccomp($amount, '0', 2) <= 0) {
                continue;
            }

            $activeCategoryIds[] = $allocation->category_id;
            $category = $allocation->category;
            $bankId = $category?->bank_id;

            $existing = IncomeDistributionTodo::query()
                ->where('income_period_id', $period->id)
                ->where('category_id', $allocation->category_id)
                ->first();

            $attributes = [
                'bank_id' => $bankId,
                'amount_encrypted' => $amount,
            ];

            if ($existing === null) {
                IncomeDistributionTodo::query()->create([
                    'income_period_id' => $period->id,
                    'category_id' => $allocation->category_id,
                    ...$attributes,
                    'status' => IncomeDistributionTodoStatus::Pending,
                ]);

                continue;
            }

            $amountChanged = bccomp($existing->amount_encrypted ?? '0', $amount, 2) !== 0;

            if ($amountChanged) {
                $attributes['status'] = IncomeDistributionTodoStatus::Pending;
                $attributes['completed_at'] = null;
                $attributes['completed_by_user_id'] = null;
            }

            $existing->update($attributes);
        }

        IncomeDistributionTodo::query()
            ->where('income_period_id', $period->id)
            ->whereNotIn('category_id', $activeCategoryIds)
            ->delete();
    }

    public function complete(User $user, IncomeDistributionTodo $todo): IncomeDistributionTodo
    {
        if ($todo->status === IncomeDistributionTodoStatus::Completed) {
            throw ValidationException::withMessages([
                'todo' => __('This transfer is already marked complete.'),
            ]);
        }

        $todo->update([
            'status' => IncomeDistributionTodoStatus::Completed,
            'completed_at' => now(),
            'completed_by_user_id' => $user->id,
        ]);

        return $todo->fresh(['category', 'bank.institution']);
    }

    /**
     * @return list<array{
     *     id: string,
     *     categoryId: string,
     *     categoryName: string,
     *     bankId: string|null,
     *     bankDisplayName: string|null,
     *     bankLogoUrl: string|null,
     *     amount: string|null,
     *     status: string,
     *     completedAt: string|null
     * }>
     */
    public function summaryForPeriod(IncomePeriod $period): array
    {
        return $this->summariesForPeriods(collect([$period]))->get($period->id, []);
    }

    /**
     * @param  Collection<int, IncomePeriod>  $periods
     * @return Collection<string, list<array{
     *     id: string,
     *     categoryId: string,
     *     categoryName: string,
     *     bankId: string|null,
     *     bankDisplayName: string|null,
     *     bankLogoUrl: string|null,
     *     amount: string|null,
     *     status: string,
     *     completedAt: string|null
     * }>>
     */
    public function summariesForPeriods(Collection $periods): Collection
    {
        if ($periods->isEmpty()) {
            return collect();
        }

        $periodIds = $periods->pluck('id');

        $todos = IncomeDistributionTodo::query()
            ->whereIn('income_period_id', $periodIds)
            ->with(['category', 'bank.institution'])
            ->get()
            ->groupBy('income_period_id');

        return $periods->mapWithKeys(function (IncomePeriod $period) use ($todos) {
            $periodTodos = $todos->get($period->id, collect())
                ->sortBy(fn (IncomeDistributionTodo $todo) => $todo->category?->sort_order ?? 0)
                ->values()
                ->map(fn (IncomeDistributionTodo $todo) => $this->todoSummary($todo))
                ->all();

            return [$period->id => $periodTodos];
        });
    }

    /**
     * @return array{
     *     pendingCount: int,
     *     totalCount: int,
     *     complete: bool
     * }
     */
    public function progressForPeriod(IncomePeriod $period): array
    {
        $summaries = $this->summaryForPeriod($period);
        $totalCount = count($summaries);
        $pendingCount = collect($summaries)
            ->where('status', IncomeDistributionTodoStatus::Pending->value)
            ->count();

        return [
            'pendingCount' => $pendingCount,
            'totalCount' => $totalCount,
            'complete' => $totalCount > 0 && $pendingCount === 0,
        ];
    }

    /**
     * @param  Collection<int, IncomePeriod>  $periods
     * @return array<string, array{pendingCount: int, totalCount: int, complete: bool}>
     */
    public function progressForPeriods(Collection $periods): array
    {
        if ($periods->isEmpty()) {
            return [];
        }

        $periodIds = $periods->pluck('id');

        $todosByPeriod = IncomeDistributionTodo::query()
            ->whereIn('income_period_id', $periodIds)
            ->get()
            ->groupBy('income_period_id');

        return $periods->mapWithKeys(function (IncomePeriod $period) use ($todosByPeriod) {
            $periodTodos = $todosByPeriod->get($period->id, collect());
            $totalCount = $periodTodos->count();
            $pendingCount = $periodTodos
                ->where('status', IncomeDistributionTodoStatus::Pending)
                ->count();

            return [
                $period->id => [
                    'pendingCount' => $pendingCount,
                    'totalCount' => $totalCount,
                    'complete' => $totalCount > 0 && $pendingCount === 0,
                ],
            ];
        })->all();
    }

    /**
     * @return array{
     *     id: string,
     *     categoryId: string,
     *     categoryName: string,
     *     bankId: string|null,
     *     bankDisplayName: string|null,
     *     bankLogoUrl: string|null,
     *     amount: string|null,
     *     status: string,
     *     completedAt: string|null
     * }
     */
    private function todoSummary(IncomeDistributionTodo $todo): array
    {
        $bank = $todo->bank;

        return [
            'id' => $todo->id,
            'categoryId' => $todo->category_id,
            'categoryName' => $todo->category?->name ?? '',
            'bankId' => $todo->bank_id,
            'bankDisplayName' => $bank !== null ? $bank->displayLabel() : null,
            'bankLogoUrl' => $bank?->institution?->logo_url,
            'amount' => $todo->amount_encrypted,
            'status' => $todo->status->value,
            'completedAt' => $todo->completed_at?->toIso8601String(),
        ];
    }
}
