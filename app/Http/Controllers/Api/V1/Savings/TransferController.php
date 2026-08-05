<?php

namespace App\Http\Controllers\Api\V1\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveFundTransferRequest;
use App\Http\Resources\FundTransferResource;
use App\Models\FundTransfer;
use App\Models\SavingsCategory;
use App\Models\Team;
use App\Services\Marketing\ActivationGhlTagService;
use App\Services\Savings\FundTransferService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransferController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundTransferService $fundTransferService,
        private ActivationGhlTagService $activationGhlTagService,
    ) {}

    public function index(Request $request, Team $team)
    {
        $plan = $this->planService->forTeam($team, $request->user());
        $transfers = $this->fundTransferService->recentForPlan($plan);

        return FundTransferResource::collection($transfers);
    }

    public function store(SaveFundTransferRequest $request, Team $team): JsonResponse
    {
        $plan = $this->planService->forTeam($team, $request->user());
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
        $this->activationGhlTagService->syncFirstTransfer($request->user(), $team);

        return response()->json(['data' => new FundTransferResource($transfer)], 201);
    }

    public function confirm(Request $request, Team $team, FundTransfer $fundTransfer): JsonResponse
    {
        $plan = $this->planService->forTeam($team, $request->user());
        abort_if($fundTransfer->savings_plan_id !== $plan->id, 404);

        return response()->json(['data' => new FundTransferResource(
            $this->fundTransferService->confirm($fundTransfer, $request->user()),
        )]);
    }

    private function assertCategoryBelongsToPlan(string $planId, string $categoryId, string $field): void
    {
        if (! SavingsCategory::query()->where('plan_id', $planId)->where('id', $categoryId)->exists()) {
            throw ValidationException::withMessages([
                $field => __('The selected fund bucket is not part of your savings plan.'),
            ]);
        }
    }
}
