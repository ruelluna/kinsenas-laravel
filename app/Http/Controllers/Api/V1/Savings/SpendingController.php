<?php

namespace App\Http\Controllers\Api\V1\Savings;

use App\Http\Controllers\Controller;
use App\Http\Resources\FundSpendResource;
use App\Models\Team;
use App\Services\Savings\FundSpendService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\Request;

class SpendingController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundSpendService $fundSpendService,
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
}
