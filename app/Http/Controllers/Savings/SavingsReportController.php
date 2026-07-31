<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Transfer;
use App\Services\Savings\SavingsPlanService;
use App\Services\Savings\TransferService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavingsReportController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private TransferService $transferService,
    ) {
    }

    public function __invoke(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);

        $transfers = Transfer::query()
            ->whereHas('incomePeriod', fn ($q) => $q->where('plan_id', $plan->id))
            ->with(['bank', 'recipient', 'category'])
            ->get();

        return Inertia::render('savings/reports', [
            'totals' => $this->transferService->reportTotals($transfers),
        ]);
    }
}
