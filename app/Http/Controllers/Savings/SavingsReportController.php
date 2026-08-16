<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Models\FundSpend;
use App\Models\Team;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\FundGraphService;
use App\Services\Savings\SavingsPlanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavingsReportController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundBalanceService $fundBalanceService,
        private FundGraphService $fundGraphService,
    ) {}

    public function __invoke(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());

        $spends = FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->with(['bank', 'recipient', 'category'])
            ->get();

        $from = $request->query('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : null;

        return Inertia::render('savings/reports', [
            'totals' => $this->fundBalanceService->reportTotals($plan, $spends),
            'graphs' => $this->fundGraphService->graphDataForPlan($plan, $from, $to),
        ]);
    }
}
