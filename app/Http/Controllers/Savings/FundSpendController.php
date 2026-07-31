<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveFundSpendRequest;
use App\Models\FundSpend;
use App\Models\Team;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\FundSpendService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class FundSpendController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundBalanceService $balanceService,
        private FundSpendService $fundSpendService,
    ) {
    }

    public function index(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);

        $plan->load('categories');
        $spends = $this->fundSpendService->recentForPlan($plan);
        $defaultCategoryId = $this->balanceService->defaultCategoryId($plan);

        return Inertia::render('savings/spending/index', [
            'plan' => ['id' => $plan->id, 'name' => $plan->name, 'hasLockedIncome' => $plan->hasLockedIncomePeriod()],
            'fundBalances' => $this->balanceService->balancesForPlan($plan),
            'defaultCategoryId' => $defaultCategoryId,
            'banks' => $current_team->banks()->where('is_active', true)->get(['id', 'name']),
            'recipients' => $current_team->recipients()->get(['id', 'name']),
            'categories' => $plan->categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
            ]),
            'spends' => $spends->map(fn (FundSpend $spend) => $this->spendPayload($spend)),
        ]);
    }

    public function store(SaveFundSpendRequest $request, Team $current_team): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);

        $categoryId = $request->validated('category_id');
        $this->assertCategoryBelongsToPlan($plan->id, $categoryId);

        $this->fundSpendService->create(
            $plan,
            $categoryId,
            $request->validated('amount'),
            $request->validated('description'),
            $request->validated('spent_on'),
            $request->validated('bank_id'),
            $request->validated('recipient_id'),
            $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Spending recorded.')]);

        return back();
    }

    public function confirm(Request $request, Team $current_team, FundSpend $fundSpend): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);
        abort_if($fundSpend->savings_plan_id !== $plan->id, 404);

        $this->fundSpendService->confirm($fundSpend, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Spending confirmed.')]);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function spendPayload(FundSpend $spend): array
    {
        return [
            'id' => $spend->id,
            'amount' => $spend->amount_encrypted,
            'description' => $spend->description,
            'status' => $spend->status->value,
            'spentOn' => $spend->spent_on->toDateString(),
            'bankName' => $spend->bank?->name,
            'recipientName' => $spend->recipient?->name,
            'categoryName' => $spend->category?->name,
            'categoryId' => $spend->category_id,
        ];
    }

    private function assertCategoryBelongsToPlan(string $planId, string $categoryId): void
    {
        $exists = \App\Models\SavingsCategory::query()
            ->where('plan_id', $planId)
            ->where('id', $categoryId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'category_id' => __('The selected fund is not part of your savings plan.'),
            ]);
        }
    }
}
