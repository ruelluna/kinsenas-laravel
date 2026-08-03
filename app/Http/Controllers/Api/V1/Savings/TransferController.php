<?php

namespace App\Http\Controllers\Api\V1\Savings;

use App\Http\Controllers\Controller;
use App\Http\Resources\FundTransferResource;
use App\Models\Team;
use App\Services\Savings\FundTransferService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundTransferService $fundTransferService,
    ) {}

    public function index(Request $request, Team $team)
    {
        $plan = $this->planService->forTeam($team, $request->user());
        $transfers = $this->fundTransferService->recentForPlan($plan);

        return FundTransferResource::collection($transfers);
    }
}
