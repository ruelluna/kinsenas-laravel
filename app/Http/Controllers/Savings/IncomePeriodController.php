<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveIncomePeriodDeductionsRequest;
use App\Http\Requests\Savings\SaveIncomePeriodRequest;
use App\Models\IncomePeriod;
use App\Models\SavingsCategory;
use App\Models\Team;
use App\Services\Marketing\ActivationGhlTagService;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\IncomeCalculationService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncomePeriodController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private IncomeCalculationService $incomeService,
        private FundBalanceService $fundBalanceService,
        private ActivationGhlTagService $activationGhlTagService,
    ) {}

    public function index(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());

        abort_if($plan === null, 404);

        $plan->load(['categories.deductFromCategory']);

        $periods = $plan->incomePeriods()
            ->with(['allocations', 'periodDeductions'])
            ->orderByDesc('period_start')
            ->get();

        $planCategories = $plan->categories
            ->sortBy('sort_order')
            ->values()
            ->map(fn (SavingsCategory $category) => $this->planCategorySummary($category))
            ->all();

        $periodRows = $periods->map(function (IncomePeriod $period) use ($planCategories) {
            $breakdown = $this->incomeService->breakdownForPeriod($period);
            $amountsByCategory = collect($breakdown)->keyBy('categoryId');

            return [
                ...$this->periodSummary($period),
                'categoryAmounts' => collect($planCategories)
                    ->mapWithKeys(fn (array $category) => [
                        $category['id'] => $amountsByCategory->get($category['id'])['amount'] ?? null,
                    ])
                    ->all(),
            ];
        });

        $fundSummary = null;

        if ($plan->shouldShowFundBalances()) {
            $balances = $this->fundBalanceService->balancesForPlan($plan);

            $fundSummary = [
                'categorySpent' => collect($balances)
                    ->mapWithKeys(fn (array $balance) => [$balance['categoryId'] => $balance['spent']])
                    ->all(),
                'categoryRemaining' => collect($balances)
                    ->mapWithKeys(fn (array $balance) => [$balance['categoryId'] => $balance['remaining']])
                    ->all(),
            ];
        }

        return Inertia::render('savings/income/index', [
            'plan' => ['id' => $plan->id, 'name' => $plan->name],
            'planCategories' => $planCategories,
            'periods' => $periodRows,
            'fundSummary' => $fundSummary,
        ]);
    }

    public function show(Request $request, Team $current_team, IncomePeriod $incomePeriod): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());

        abort_if($plan === null, 404);
        abort_if($incomePeriod->plan_id !== $plan->id, 404);

        $incomePeriod->load(['plan', 'allocations.category']);

        return Inertia::render('savings/income/show', [
            'plan' => ['id' => $plan->id, 'name' => $plan->name],
            'period' => $this->periodSummary($incomePeriod),
            'breakdown' => $this->incomeService->breakdownForPeriod($incomePeriod),
            'customCategories' => $this->incomeService->customCategoriesForPeriod($incomePeriod),
            'fundBalances' => $plan->shouldShowFundBalances()
                ? $this->fundBalanceService->balancesForPlan($plan)
                : [],
        ]);
    }

    public function updateCustomAmounts(
        SaveIncomePeriodDeductionsRequest $request,
        Team $current_team,
        IncomePeriod $incomePeriod,
    ): RedirectResponse {
        abort_if($incomePeriod->plan_id !== $this->planService->forTeam($current_team, $request->user())?->id, 404);

        $this->incomeService->syncCustomAmounts($incomePeriod, $request->validated('custom_amounts'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Custom amounts updated.')]);

        return back();
    }

    public function store(SaveIncomePeriodRequest $request, Team $current_team): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);

        $this->incomeService->create(
            $plan,
            $request->validated('name'),
            $request->validated('amount'),
            $request->validated('period_start'),
        );

        $this->activationGhlTagService->syncFirstIncomeEntered($request->user(), $current_team);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Income period saved.')]);

        return back();
    }

    public function lock(Request $request, Team $current_team, IncomePeriod $incomePeriod): RedirectResponse
    {
        abort_if($incomePeriod->plan_id !== $this->planService->forTeam($current_team, $request->user())?->id, 404);

        $this->incomeService->lock($incomePeriod, $request->user());

        $this->activationGhlTagService->syncIncomeLocked($request->user(), $current_team);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Income locked.')]);

        return back();
    }

    public function unlock(Request $request, Team $current_team, IncomePeriod $incomePeriod): RedirectResponse
    {
        abort_if($incomePeriod->plan_id !== $this->planService->forTeam($current_team, $request->user())?->id, 404);

        $this->incomeService->unlock($incomePeriod);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Income unlocked.')]);

        return back();
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     allocationType: string,
     *     percentage: string|null,
     *     deductionMode: string|null,
     *     deductionValue: string|null
     * }
     */
    private function planCategorySummary(SavingsCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'allocationType' => $category->allocation_type->value,
            'percentage' => $category->percentage !== null ? (string) $category->percentage : null,
            'deductionMode' => $category->deduction_mode?->value,
            'deductionValue' => $category->deduction_value !== null ? (string) $category->deduction_value : null,
        ];
    }

    /**
     * @return array{id: string, name: string, periodStart: string, amount: string|null, isLocked: bool}
     */
    private function periodSummary(IncomePeriod $period): array
    {
        return [
            'id' => $period->id,
            'name' => $period->name,
            'periodStart' => $period->period_start->toDateString(),
            'amount' => $period->amount_encrypted,
            'isLocked' => $period->is_locked,
        ];
    }
}
