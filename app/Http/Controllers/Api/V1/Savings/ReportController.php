<?php

namespace App\Http\Controllers\Api\V1\Savings;

use App\Http\Controllers\Controller;
use App\Models\FundSpend;
use App\Models\Team;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundBalanceService $fundBalanceService,
    ) {}

    public function index(Request $request, Team $team): JsonResponse
    {
        $plan = $this->planService->forTeam($team, $request->user());
        $spends = FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->with(['bank', 'recipient', 'category'])
            ->get();

        return response()->json([
            'data' => $this->fundBalanceService->reportTotals($plan, $spends),
        ]);
    }
}
