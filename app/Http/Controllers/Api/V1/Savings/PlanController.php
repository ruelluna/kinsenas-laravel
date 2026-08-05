<?php

namespace App\Http\Controllers\Api\V1\Savings;

use App\Enums\TeamPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\AddCategoryOpeningBalanceRequest;
use App\Http\Requests\Savings\SaveSavingsPlanRequest;
use App\Models\SavingsCategory;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Services\Marketing\ActivationGhlTagService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private ActivationGhlTagService $activationGhlTagService,
    ) {}

    public function show(Request $request, Team $team): JsonResponse
    {
        return response()->json([
            'data' => $this->planPayload($this->planService->forTeam($team, $request->user())),
        ]);
    }

    public function storeFromTemplate(Request $request, Team $team, SavingsFormulaTemplate $template): JsonResponse
    {
        $plan = $this->planService->cloneFromTemplate($team, $request->user(), $template, $template->name);
        $this->activationGhlTagService->syncPlanCreated($request->user(), $team, $template->slug);

        return response()->json(['data' => $this->planPayload($plan)], 201);
    }

    public function storeCustom(Request $request, Team $team): JsonResponse
    {
        $plan = $this->planService->createCustom($team, $request->user());
        $this->activationGhlTagService->syncPlanCreated($request->user(), $team, 'custom');

        return response()->json(['data' => $this->planPayload($plan)], 201);
    }

    public function update(SaveSavingsPlanRequest $request, Team $team): JsonResponse
    {
        $plan = $this->planForUpdate($request, $team);
        $plan = $this->planService->updateCategories($plan, $request->validated('categories'));

        if ($request->has('is_shared_with_team')) {
            $plan = $this->planService->updateShareSetting($plan, $request->boolean('is_shared_with_team'));
        }

        if ($request->has('allow_editing_spends')) {
            $plan = $this->planService->updateSpendingEditSetting($plan, $request->boolean('allow_editing_spends'));
        }

        return response()->json(['data' => $this->planPayload($plan)]);
    }

    public function addOpeningBalance(
        AddCategoryOpeningBalanceRequest $request,
        Team $team,
        SavingsCategory $category,
    ): JsonResponse {
        $plan = $this->planForUpdate($request, $team);
        $category = $this->planService->addOpeningBalance($plan, $category, $request->validated('amount'));

        return response()->json([
            'data' => [
                'id' => $category->id,
                'openingBalance' => $category->opening_balance_encrypted,
            ],
        ]);
    }

    public function destroy(Request $request, Team $team): JsonResponse
    {
        $plan = $this->planService->forTeam($team, $request->user());

        abort_if($plan === null, 404);
        abort_if(! $request->user()->can('delete', [$plan, $team]), 403);

        $this->planService->discardDraft($plan);

        return response()->noContent();
    }

    private function planForUpdate(Request $request, Team $team): SavingsPlan
    {
        $plan = $this->planService->forTeam($team, $request->user());

        abort_if($plan === null, 404);
        abort_if(
            $plan->created_by_user_id !== $request->user()->id
                && ! ($plan->is_shared_with_team && $request->user()->hasTeamPermission($team, TeamPermission::UpdateTeam)),
            403,
        );

        return $plan;
    }

    private function planPayload(?SavingsPlan $plan): ?array
    {
        if ($plan === null) {
            return null;
        }

        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'currency' => $plan->currency,
            'isSharedWithTeam' => $plan->is_shared_with_team,
            'allowEditingSpends' => $plan->allow_editing_spends,
            'categories' => $plan->categories->map(fn (SavingsCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'allocationType' => $category->allocation_type->value,
                'percentage' => $category->percentage !== null ? (string) $category->percentage : null,
                'deductionMode' => $category->deduction_mode?->value,
                'deductionValue' => $category->deduction_value !== null ? (string) $category->deduction_value : null,
                'deductFromCategoryId' => $category->deduct_from_category_id,
                'bankId' => $category->bank_id,
                'openingBalance' => $category->opening_balance_encrypted,
            ])->values()->all(),
            'hasIncome' => $plan->hasIncomePeriod(),
            'canDrawFromFunds' => $plan->canDrawFromFunds(),
        ];
    }
}
