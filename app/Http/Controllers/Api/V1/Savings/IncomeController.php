<?php

namespace App\Http\Controllers\Api\V1\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\CompleteIncomeDistributionTodoRequest;
use App\Http\Requests\Savings\SaveIncomePeriodDeductionsRequest;
use App\Http\Requests\Savings\SaveIncomePeriodRequest;
use App\Http\Resources\IncomePeriodResource;
use App\Models\IncomeDistributionTodo;
use App\Models\IncomePeriod;
use App\Models\Team;
use App\Services\Marketing\ActivationGhlTagService;
use App\Services\Savings\IncomeCalculationService;
use App\Services\Savings\IncomeDistributionTodoService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private IncomeCalculationService $incomeService,
        private IncomeDistributionTodoService $distributionTodoService,
        private ActivationGhlTagService $activationGhlTagService,
    ) {}

    public function index(Request $request, Team $team)
    {
        $plan = $this->planService->forTeam($team, $request->user());

        $periods = $plan->incomePeriods()
            ->orderByDesc('period_start')
            ->get();

        return IncomePeriodResource::collection($periods);
    }

    public function show(Request $request, Team $team, IncomePeriod $incomePeriod): JsonResponse
    {
        $this->assertBelongsToTeam($request, $team, $incomePeriod);

        return response()->json([
            'data' => (new IncomePeriodResource($incomePeriod->load('allocations.category')))->resolve(),
            'breakdown' => $this->incomeService->breakdownForPeriod($incomePeriod),
            'customCategories' => $this->incomeService->customCategoriesForPeriod($incomePeriod),
            'distributionTodos' => $this->distributionTodoService->summaryForPeriod($incomePeriod),
            'distributionTodoProgress' => $this->distributionTodoService->progressForPeriod($incomePeriod),
        ]);
    }

    public function store(SaveIncomePeriodRequest $request, Team $team): JsonResponse
    {
        $plan = $this->planService->forTeam($team, $request->user());
        $period = $this->incomeService->create(
            $plan,
            $request->user(),
            $request->validated('name'),
            $request->validated('amount'),
            $request->validated('period_start'),
        );
        $this->activationGhlTagService->syncFirstIncomeEntered($request->user(), $team);
        $this->activationGhlTagService->syncIncomeLocked($request->user(), $team);

        return response()->json(['data' => new IncomePeriodResource($period)], 201);
    }

    public function updateCustomAmounts(
        SaveIncomePeriodDeductionsRequest $request,
        Team $team,
        IncomePeriod $incomePeriod,
    ): JsonResponse {
        $this->assertBelongsToTeam($request, $team, $incomePeriod);
        $period = $this->incomeService->syncCustomAmounts(
            $incomePeriod,
            $request->user(),
            $request->validated('custom_amounts'),
        );

        return response()->json(['data' => new IncomePeriodResource($period)]);
    }

    public function completeDistributionTodo(
        CompleteIncomeDistributionTodoRequest $request,
        Team $team,
        IncomePeriod $incomePeriod,
        IncomeDistributionTodo $todo,
    ): JsonResponse {
        $this->assertBelongsToTeam($request, $team, $incomePeriod);
        $todo = $this->distributionTodoService->complete($request->user(), $todo);

        return response()->json(['data' => [
            'id' => $todo->id,
            'status' => $todo->status->value,
            'completedAt' => $todo->completed_at?->toIso8601String(),
        ]]);
    }

    public function destroy(Request $request, Team $team, IncomePeriod $incomePeriod): JsonResponse
    {
        $this->assertBelongsToTeam($request, $team, $incomePeriod);
        $this->incomeService->delete($incomePeriod);

        return response()->noContent();
    }

    private function assertBelongsToTeam(Request $request, Team $team, IncomePeriod $incomePeriod): void
    {
        abort_if($incomePeriod->plan_id !== $this->planService->forTeam($team, $request->user())?->id, 404);
    }
}
