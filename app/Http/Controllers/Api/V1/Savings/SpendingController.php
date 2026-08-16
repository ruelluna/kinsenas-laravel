<?php

namespace App\Http\Controllers\Api\V1\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\RecordFundSpendReimbursementRequest;
use App\Http\Requests\Savings\SaveFundSpendRequest;
use App\Http\Requests\Savings\UpdateFundSpendRequest;
use App\Http\Resources\FundSpendResource;
use App\Models\FundSpend;
use App\Models\SavingsCategory;
use App\Models\Team;
use App\Services\Marketing\ActivationGhlTagService;
use App\Services\Savings\FundSpendReimbursementService;
use App\Services\Savings\FundSpendService;
use App\Services\Savings\FundTransferService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SpendingController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundSpendService $fundSpendService,
        private FundTransferService $fundTransferService,
        private FundSpendReimbursementService $reimbursementService,
        private ActivationGhlTagService $activationGhlTagService,
    ) {}

    public function index(Request $request, Team $team)
    {
        $plan = $this->planService->forTeam($team, $request->user());
        $spends = $this->fundSpendService->recentForPlan($plan);

        return FundSpendResource::collection($spends)->additional([
            'meta' => [
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                ],
            ],
        ]);
    }

    public function store(SaveFundSpendRequest $request, Team $team): JsonResponse
    {
        $plan = $this->planService->forTeam($team, $request->user());
        $categoryId = $request->validated('category_id');
        $this->assertCategoryBelongsToPlan($plan->id, $categoryId);

        $bankId = $request->validated('bank_id');
        if ($bankId !== null) {
            abort_if(! $team->banks()->where('id', $bankId)->exists(), 404);
            $this->fundTransferService->assertBankAllowedForCategory($plan, $categoryId, $bankId);
        }

        $spend = $this->fundSpendService->create(
            $plan,
            $categoryId,
            $request->validated('amount'),
            $request->validated('description'),
            $request->validated('spent_on'),
            $bankId,
            $request->validated('recipient_id'),
            $request->user(),
            $request->file('receipt_image')?->store('spending-receipts', 'public'),
            $request->boolean('expects_reimbursement'),
            $request->validated('expected_from_recipient_id'),
        );
        $this->activationGhlTagService->syncFirstSpend($request->user(), $team);

        return response()->json(['data' => new FundSpendResource($spend->load(['bank', 'recipient', 'category', 'expectedFromRecipient', 'reimbursements.bank']))], 201);
    }

    public function update(UpdateFundSpendRequest $request, Team $team, FundSpend $fundSpend): JsonResponse
    {
        $plan = $this->planService->forTeam($team, $request->user());
        abort_if($fundSpend->savings_plan_id !== $plan->id, 404);

        $categoryId = $request->validated('category_id');
        $this->assertCategoryBelongsToPlan($plan->id, $categoryId);
        $spend = $this->fundSpendService->update(
            $fundSpend,
            $plan,
            $categoryId,
            $request->validated('amount'),
            $request->validated('description'),
            $request->validated('spent_on'),
            $request->validated('recipient_id'),
            $request->file('receipt_image')?->store('spending-receipts', 'public'),
            $request->boolean('remove_receipt'),
            $request->boolean('expects_reimbursement'),
            $request->validated('expected_from_recipient_id'),
        );

        return response()->json(['data' => new FundSpendResource($spend)]);
    }

    public function destroy(Request $request, Team $team, FundSpend $fundSpend): JsonResponse
    {
        $plan = $this->planService->forTeam($team, $request->user());
        abort_if($fundSpend->savings_plan_id !== $plan->id, 404);

        $this->fundSpendService->delete($fundSpend, $plan);

        return response()->noContent();
    }

    public function confirm(Request $request, Team $team, FundSpend $fundSpend): JsonResponse
    {
        $plan = $this->planService->forTeam($team, $request->user());
        abort_if($fundSpend->savings_plan_id !== $plan->id, 404);

        return response()->json(['data' => new FundSpendResource(
            $this->fundSpendService->confirm($fundSpend, $request->user()),
        )]);
    }

    public function storeReimbursement(
        RecordFundSpendReimbursementRequest $request,
        Team $team,
        FundSpend $fundSpend,
    ): JsonResponse {
        $plan = $this->planService->forTeam($team, $request->user());
        abort_if($fundSpend->savings_plan_id !== $plan->id, 404);

        $bankId = $request->validated('bank_id');
        if ($bankId !== null) {
            abort_if(! $team->banks()->where('id', $bankId)->exists(), 404);
        }

        $this->reimbursementService->record(
            $fundSpend,
            $request->validated('amount'),
            $request->validated('received_on'),
            $bankId,
            $request->validated('notes'),
            $request->user(),
        );

        return response()->json([
            'data' => new FundSpendResource($fundSpend->fresh(['bank', 'recipient', 'category', 'expectedFromRecipient', 'reimbursements.bank'])),
        ]);
    }

    public function closeReimbursement(Request $request, Team $team, FundSpend $fundSpend): JsonResponse
    {
        $plan = $this->planService->forTeam($team, $request->user());
        abort_if($fundSpend->savings_plan_id !== $plan->id, 404);

        return response()->json([
            'data' => new FundSpendResource(
                $this->reimbursementService->closeExpectation($fundSpend),
            ),
        ]);
    }

    private function assertCategoryBelongsToPlan(string $planId, string $categoryId): void
    {
        if (! SavingsCategory::query()->where('plan_id', $planId)->where('id', $categoryId)->exists()) {
            throw ValidationException::withMessages([
                'category_id' => __('The selected fund bucket is not part of your savings plan.'),
            ]);
        }
    }
}
