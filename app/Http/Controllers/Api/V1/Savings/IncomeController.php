<?php

namespace App\Http\Controllers\Api\V1\Savings;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncomePeriodResource;
use App\Models\Team;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function __construct(private SavingsPlanService $planService) {}

    public function index(Request $request, Team $team)
    {
        $plan = $this->planService->forTeam($team, $request->user());

        $periods = $plan->incomePeriods()
            ->orderByDesc('period_start')
            ->get();

        return IncomePeriodResource::collection($periods);
    }
}
