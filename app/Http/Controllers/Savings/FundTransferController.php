<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveFundTransferRequest;
use App\Models\FundTransfer;
use App\Models\SavingsCategory;
use App\Models\Team;
use App\Services\Marketing\ActivationGhlTagService;
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
        private ActivationGhlTagService $activationGhlTagService,
    ) {}

    public function index(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);

        $plan->load('categories.bank.institution');
        $transfers = $this->fundTransferService->recentForPlan($plan);
        $defaultCategoryId = $this->balanceService->defaultCategoryId($plan);
        $categories = $this->balanceService->categoriesWithDefaultFirst($plan);

        return Inertia::render('savings/transfers/index', [
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'hasLockedIncome' => $plan->hasLockedIncomePeriod(),
                'canDrawFromFunds' => $plan->canDrawFromFunds(),
            ],
            'fundBalances' => $plan->shouldShowFundBalances()
                ? $this->balanceService->balancesWithDefaultFirst($plan)
                : [],
            'defaultCategoryId' => $defaultCategoryId,
            'categories' => $categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'bankId' => $category->bank_id,
                'bankName' => $category->bank?->name,
                'bankLogoUrl' => $category->bank?->institution?->logo_url,
            ]),
            'categoryBankMap' => $this->balanceService->categoryBankMap($plan),
            'transfers' => $transfers->map(fn (FundTransfer $transfer) => $this->transferPayload($transfer)),
        ]);
    }

    public function store(SaveFundTransferRequest $request, Team $current_team): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);

        $fromCategoryId = $request->validated('from_category_id');
        $toCategoryId = $request->validated('to_category_id');
        $this->assertCategoryBelongsToPlan($plan->id, $fromCategoryId, 'from_category_id');
        $this->assertCategoryBelongsToPlan($plan->id, $toCategoryId, 'to_category_id');

        $transfer = $this->fundTransferService->create(
            $plan,
            $fromCategoryId,
            $toCategoryId,
            $request->validated('amount'),
            $request->validated('description'),
            $request->validated('transferred_on'),
            $request->user(),
        );

        $this->activationGhlTagService->syncFirstTransfer($request->user(), $current_team);

        $message = $transfer->isConfirmed()
            ? __('Transfer recorded.')
            : __('Transfer recorded — move the funds between banks, then confirm.');

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

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
            'fromCategoryName' => $transfer->fromCategory?->name,
            'toCategoryName' => $transfer->toCategory?->name,
            'fromCategoryId' => $transfer->from_category_id,
            'toCategoryId' => $transfer->to_category_id,
            'fromBankName' => $transfer->fromBank?->name,
            'toBankName' => $transfer->toBank?->name,
            'fromBankLogoUrl' => $transfer->fromBank?->institution?->logo_url,
            'toBankLogoUrl' => $transfer->toBank?->institution?->logo_url,
            'crossesBanks' => $transfer->crossesBanks(),
        ];
    }

    private function assertCategoryBelongsToPlan(string $planId, string $categoryId, string $field): void
    {
        $exists = SavingsCategory::query()
            ->where('plan_id', $planId)
            ->where('id', $categoryId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                $field => __('The selected fund bucket is not part of your savings plan.'),
            ]);
        }
    }
}
