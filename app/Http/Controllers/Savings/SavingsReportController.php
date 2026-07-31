<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Models\FundSpend;
use App\Models\Team;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavingsReportController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundBalanceService $fundBalanceService,
    ) {
    }

    public function __invoke(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);

        $spends = FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->with(['bank', 'recipient', 'category'])
            ->get();

        return Inertia::render('savings/reports', [
            'totals' => $this->fundBalanceService->reportTotals($plan, $spends),
        ]);
    }
}
