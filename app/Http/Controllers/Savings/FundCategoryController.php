<?php

namespace App\Http\Controllers\Savings;

use App\Enums\SubscriptionFeature;
use App\Http\Controllers\Controller;
use App\Models\SavingsCategory;
use App\Models\Team;
use App\Services\Billing\SubscriptionService;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\FundCategoryDetailService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FundCategoryController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundBalanceService $balanceService,
        private FundCategoryDetailService $detailService,
        private SubscriptionService $subscriptionService,
    ) {}

    public function show(Request $request, Team $current_team, string $category): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());

        $categoryModel = SavingsCategory::query()
            ->where('plan_id', $plan->id)
            ->where('id', $category)
            ->firstOrFail();

        $plan->load('categories.bank.institution');
        $categoryModel->load('bank.institution');

        $fundBalance = $this->detailService->balanceForCategory($plan, $categoryModel->id);

        if ($fundBalance === null) {
            abort(404);
        }

        $categories = $this->detailService->categoryOptions($plan);

        return Inertia::render('savings/funds/show', [
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'canDrawFromFunds' => $plan->canDrawFromFunds(),
                'allowEditingSpends' => $plan->allow_editing_spends,
            ],
            'fundBalance' => $fundBalance,
            'fundAddedEntries' => $this->detailService->fundAddedEntriesForCategory($plan, $categoryModel->id),
            'allocations' => $this->detailService->allocationsForCategory($plan, $categoryModel->id),
            'transfers' => $this->detailService->transfersForCategory($plan, $categoryModel->id),
            'spends' => $this->detailService->spendsForCategory($plan, $categoryModel->id),
            'defaultCategoryId' => $this->balanceService->defaultCategoryId($plan),
            'recipients' => $current_team->recipients()->get(['id', 'name']),
            'categories' => $categories->map(fn (SavingsCategory $row) => [
                'id' => $row->id,
                'name' => $row->name,
                'bankId' => $row->bank_id,
                'bankName' => $row->bank?->name,
                'bankLogoUrl' => $row->bank?->institution?->logo_url,
            ]),
            'categoryBankMap' => $this->balanceService->categoryBankMap($plan),
            'canTransfer' => $this->subscriptionService->userHasFeature(
                $request->user(),
                SubscriptionFeature::Transfers,
            ),
        ]);
    }
}
