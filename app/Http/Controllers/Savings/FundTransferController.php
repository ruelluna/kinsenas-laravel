<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveFundTransferRequest;
use App\Models\FundTransfer;
use App\Models\Team;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\FundTransferService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class FundTransferController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundBalanceService $balanceService,
        private FundTransferService $fundTransferService,
    ) {
    }

    public function index(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);

        $plan->load('categories.banks');
        $transfers = $this->fundTransferService->recentForPlan($plan);
        $defaultCategoryId = $this->balanceService->defaultCategoryId($plan);
        $banks = $current_team->banks()
            ->where('is_active', true)
            ->with('institution')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('savings/transfers/index', [
            'plan' => ['id' => $plan->id, 'name' => $plan->name, 'hasLockedIncome' => $plan->hasLockedIncomePeriod()],
            'fundBalances' => $this->balanceService->balancesForPlan($plan),
            'defaultCategoryId' => $defaultCategoryId,
            'banks' => $banks->map(fn ($bank) => [
                'id' => $bank->id,
                'name' => $bank->name,
                'logoUrl' => $bank->institution?->logo_url,
            ]),
            'categories' => $plan->categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'bankIds' => $category->banks->pluck('id')->all(),
            ]),
            'categoryBankMap' => $this->balanceService->categoryBankMap($plan),
            'transfers' => $transfers->map(fn (FundTransfer $transfer) => $this->transferPayload($transfer)),
        ]);
    }

    public function store(SaveFundTransferRequest $request, Team $current_team): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);

        $categoryId = $request->validated('category_id');
        $this->assertCategoryBelongsToPlan($plan->id, $categoryId);

        $bankId = $request->validated('bank_id');
        abort_if(
            ! $current_team->banks()->where('id', $bankId)->exists(),
            404,
        );

        $this->fundTransferService->create(
            $plan,
            $categoryId,
            $bankId,
            $request->validated('amount'),
            $request->validated('description'),
            $request->validated('transferred_on'),
            $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Transfer recorded — confirm when funds arrive.')]);

        return back();
    }

    public function confirm(Request $request, Team $current_team, FundTransfer $fundTransfer): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);
        abort_if($fundTransfer->savings_plan_id !== $plan->id, 404);

        $this->fundTransferService->confirm($fundTransfer, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Transfer confirmed.')]);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function transferPayload(FundTransfer $transfer): array
    {
        return [
            'id' => $transfer->id,
            'amount' => $transfer->amount_encrypted,
            'description' => $transfer->description,
            'status' => $transfer->status->value,
            'transferredOn' => $transfer->transferred_on->toDateString(),
            'bankName' => $transfer->bank?->name,
            'bankLogoUrl' => $transfer->bank?->institution?->logo_url,
            'categoryName' => $transfer->category?->name,
            'categoryId' => $transfer->category_id,
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
