<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\CompleteIncomeDistributionTodoRequest;
use App\Http\Requests\Savings\SaveIncomePeriodDeductionsRequest;
use App\Http\Requests\Savings\SaveIncomePeriodRequest;
use App\Models\IncomeDistributionTodo;
use App\Models\IncomePeriod;
use App\Models\SavingsCategory;
use App\Models\Team;
use App\Services\Marketing\ActivationGhlTagService;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\IncomeCalculationService;
use App\Services\Savings\IncomeDistributionTodoService;
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
        private IncomeDistributionTodoService $distributionTodoService,
    ) {}

    public function index(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());

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

        $distributionTodoProgress = $this->distributionTodoService->progressForPeriods($periods);

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
            'periods' => $periodRows->map(function (array $row) use ($distributionTodoProgress) {
                $progress = $distributionTodoProgress[$row['id']] ?? [
                    'pendingCount' => 0,
                    'totalCount' => 0,
                    'complete' => true,
                ];

                return [
                    ...$row,
                    'distributionTodoProgress' => $progress,
                ];
            }),
            'fundSummary' => $fundSummary,
        ]);
    }

    public function show(Request $request, Team $current_team, IncomePeriod $incomePeriod): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
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
            'distributionTodos' => $this->distributionTodoService->summaryForPeriod($incomePeriod),
            'distributionTodoProgress' => $this->distributionTodoService->progressForPeriod($incomePeriod),
        ]);
    }

    public function completeDistributionTodo(
        CompleteIncomeDistributionTodoRequest $request,
        Team $current_team,
        IncomePeriod $incomePeriod,
        IncomeDistributionTodo $todo,
    ): RedirectResponse {
        abort_if($incomePeriod->plan_id !== $this->planService->forTeam($current_team, $request->user())?->id, 404);

        $this->distributionTodoService->complete($request->user(), $todo);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Transfer marked complete.')]);

        return back();
    }

    public function updateCustomAmounts(
        SaveIncomePeriodDeductionsRequest $request,
        Team $current_team,
        IncomePeriod $incomePeriod,
    ): RedirectResponse {
        abort_if($incomePeriod->plan_id !== $this->planService->forTeam($current_team, $request->user())?->id, 404);

        $this->incomeService->syncCustomAmounts(
            $incomePeriod,
            $request->user(),
            $request->validated('custom_amounts'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Custom amounts updated.')]);

        return back();
    }

    public function store(SaveIncomePeriodRequest $request, Team $current_team): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());

        $this->incomeService->create(
            $plan,
            $request->user(),
            $request->validated('name'),
            $request->validated('amount'),
            $request->validated('period_start'),
        );

        $this->activationGhlTagService->syncFirstIncomeEntered($request->user(), $current_team);
        $this->activationGhlTagService->syncIncomeLocked($request->user(), $current_team);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Income period saved.')]);

        return back();
    }

    public function destroy(Request $request, Team $current_team, IncomePeriod $incomePeriod): RedirectResponse
    {
        abort_if($incomePeriod->plan_id !== $this->planService->forTeam($current_team, $request->user())?->id, 404);

        $this->incomeService->delete($incomePeriod);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Income period deleted.')]);

        return redirect()->route('savings.income.index', ['current_team' => $current_team->slug]);
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
     * @return array{id: string, name: string, periodStart: string, amount: string|null}
     */
    private function periodSummary(IncomePeriod $period): array
    {
        return [
            'id' => $period->id,
            'name' => $period->name,
            'periodStart' => $period->period_start->toDateString(),
            'amount' => $period->amount_encrypted,
        ];
    }
}
