<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveTransferRequest;
use App\Models\IncomePeriod;
use App\Models\Team;
use App\Models\Transfer;
use App\Services\Savings\SavingsPlanService;
use App\Services\Savings\TransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransferController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private TransferService $transferService,
    ) {
    }

    public function index(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        abort_if($plan === null, 404);

        $lockedPeriods = $plan->incomePeriods()->where('is_locked', true)->get();
        $transfers = Transfer::query()
            ->whereIn('income_period_id', $lockedPeriods->pluck('id'))
            ->with(['bank', 'recipient', 'category', 'incomePeriod'])
            ->latest()
            ->get();

        return Inertia::render('savings/transfers/index', [
            'lockedPeriods' => $lockedPeriods->map(fn (IncomePeriod $p) => [
                'id' => $p->id,
                'periodStart' => $p->period_start->toDateString(),
                'amount' => $p->amount_encrypted,
            ]),
            'banks' => $current_team->banks()->where('is_active', true)->get(['id', 'name']),
            'recipients' => $current_team->recipients()->get(['id', 'name']),
            'categories' => $plan->categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]),
            'transfers' => $transfers->map(fn (Transfer $t) => [
                'id' => $t->id,
                'amount' => $t->amount_encrypted,
                'status' => $t->status->value,
                'transferredOn' => $t->transferred_on->toDateString(),
                'bankName' => $t->bank?->name,
                'recipientName' => $t->recipient?->name,
                'categoryName' => $t->category?->name,
                'periodStart' => $t->incomePeriod?->period_start?->toDateString(),
            ]),
        ]);
    }

    public function store(SaveTransferRequest $request, Team $current_team): RedirectResponse
    {
        $period = IncomePeriod::query()->findOrFail($request->validated('income_period_id'));

        $this->transferService->create(
            $period,
            $request->validated('category_id'),
            $request->validated('bank_id'),
            $request->validated('recipient_id'),
            $request->validated('amount'),
            $request->validated('transferred_on'),
            $request->validated('notes'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Transfer recorded.')]);

        return back();
    }

    public function confirm(Request $request, Team $current_team, Transfer $transfer): RedirectResponse
    {
        $this->transferService->confirm($transfer, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Transfer confirmed.')]);

        return back();
    }
}
