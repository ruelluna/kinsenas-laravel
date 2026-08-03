<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveFundSpendRequest;
use App\Http\Requests\Savings\UpdateFundSpendRequest;
use App\Models\FundSpend;
use App\Models\SavingsCategory;
use App\Models\Team;
use App\Services\Marketing\ActivationGhlTagService;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\FundSpendService;
use App\Services\Savings\FundTransferService;
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
        private FundTransferService $fundTransferService,
        private ActivationGhlTagService $activationGhlTagService,
    ) {}

    public function index(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());

        $plan->load('categories.bank');
        $spends = $this->fundSpendService->recentForPlan($plan);
        $defaultCategoryId = $this->balanceService->defaultCategoryId($plan);
        $categories = $this->balanceService->categoriesWithDefaultFirst($plan);

        return Inertia::render('savings/spending/index', [
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'canDrawFromFunds' => $plan->canDrawFromFunds(),
                'allowEditingSpends' => $plan->allow_editing_spends,
            ],
            'fundBalances' => $plan->shouldShowFundBalances()
                ? $this->balanceService->balancesWithDefaultFirst($plan)
                : [],
            'defaultCategoryId' => $defaultCategoryId,
            'recipients' => $current_team->recipients()->get(['id', 'name']),
            'categories' => $categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'bankId' => $category->bank_id,
            ]),
            'spends' => $spends->map(fn (FundSpend $spend) => $this->spendPayload($spend)),
        ]);
    }

    public function store(SaveFundSpendRequest $request, Team $current_team): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());

        $categoryId = $request->validated('category_id');
        $this->assertCategoryBelongsToPlan($plan->id, $categoryId);

        $bankId = $request->validated('bank_id');
        if ($bankId !== null) {
            abort_if(
                ! $current_team->banks()->where('id', $bankId)->exists(),
                404,
            );
            $this->fundTransferService->assertBankAllowedForCategory($plan, $categoryId, $bankId);
        }

        $receiptImagePath = $request->file('receipt_image')?->store('spending-receipts', 'public');

        $this->fundSpendService->create(
            $plan,
            $categoryId,
            $request->validated('amount'),
            $request->validated('description'),
            $request->validated('spent_on'),
            $request->validated('bank_id'),
            $request->validated('recipient_id'),
            $request->user(),
            $receiptImagePath,
        );

        $this->activationGhlTagService->syncFirstSpend($request->user(), $current_team);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Spending recorded.')]);

        return back();
    }

    public function update(UpdateFundSpendRequest $request, Team $current_team, FundSpend $fundSpend): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($fundSpend->savings_plan_id !== $plan->id, 404);

        $categoryId = $request->validated('category_id');
        $this->assertCategoryBelongsToPlan($plan->id, $categoryId);

        $receiptImagePath = $request->file('receipt_image')?->store('spending-receipts', 'public');

        $this->fundSpendService->update(
            $fundSpend,
            $plan,
            $categoryId,
            $request->validated('amount'),
            $request->validated('description'),
            $request->validated('spent_on'),
            $request->validated('recipient_id'),
            $receiptImagePath,
            $request->boolean('remove_receipt'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Spending updated.')]);

        return back();
    }

    public function destroy(Request $request, Team $current_team, FundSpend $fundSpend): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($fundSpend->savings_plan_id !== $plan->id, 404);

        $this->fundSpendService->delete($fundSpend, $plan);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Spending deleted.')]);

        return back();
    }

    public function confirm(Request $request, Team $current_team, FundSpend $fundSpend): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
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
            'recipientId' => $spend->recipient_id,
            'receiptImageUrl' => $spend->receiptImageUrl(),
        ];
    }

    private function assertCategoryBelongsToPlan(string $planId, string $categoryId): void
    {
        $exists = SavingsCategory::query()
            ->where('plan_id', $planId)
            ->where('id', $categoryId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'category_id' => __('The selected fund bucket is not part of your savings plan.'),
            ]);
        }
    }
}
