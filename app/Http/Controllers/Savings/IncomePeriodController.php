<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveIncomePeriodRequest;
use App\Models\IncomePeriod;
use App\Models\Team;
use App\Services\Savings\IncomeCalculationService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncomePeriodController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private IncomeCalculationService $incomeService,
    ) {
    }

    public function index(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());

        abort_if($plan === null, 404);

        $periods = $plan->incomePeriods()->with('allocations.category')->get();

        return Inertia::render('savings/income/index', [
            'plan' => ['id' => $plan->id, 'name' => $plan->name],
            'periods' => $periods->map(fn (IncomePeriod $period) => [
                'id' => $period->id,
                'periodStart' => $period->period_start->toDateString(),
                'amount' => $period->amount_encrypted,
                'isLocked' => $period->is_locked,
                'allocations' => $period->allocations->map(fn ($a) => [
                    'categoryName' => $a->category?->name,
                    'amount' => $a->amount_encrypted,
                ]),
            ]),
        ]);
    }

    public function store(SaveIncomePeriodRequest $request, Team $current_team): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);

        $this->incomeService->create(
            $plan,
            $request->validated('amount'),
            $request->validated('period_start'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Income period saved.')]);

        return back();
    }

    public function lock(Request $request, Team $current_team, IncomePeriod $incomePeriod): RedirectResponse
    {
        abort_if($incomePeriod->plan_id !== $this->planService->forTeam($current_team, $request->user())?->id, 404);

        $this->incomeService->lock($incomePeriod, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Income locked.')]);

        return back();
    }

    public function unlock(Request $request, Team $current_team, IncomePeriod $incomePeriod): RedirectResponse
    {
        abort_if($incomePeriod->plan_id !== $this->planService->forTeam($current_team, $request->user())?->id, 404);

        $this->incomeService->unlock($incomePeriod);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Income unlocked.')]);

        return back();
    }
}
