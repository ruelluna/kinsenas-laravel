<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveBankRequest;
use App\Models\Bank;
use App\Models\BankInstitution;
use App\Models\Team;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundBalanceService $fundBalanceService,
    ) {
    }

    public function index(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        $institutions = BankInstitution::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (BankInstitution $institution) => [
                'id' => $institution->id,
                'name' => $institution->name,
                'logoUrl' => $institution->logo_url,
                'type' => $institution->type->value,
            ]);

        return Inertia::render('savings/banks/index', [
            'banks' => $current_team->banks()->with('institution')->orderBy('sort_order')->get()->map(fn (Bank $bank) => [
                'id' => $bank->id,
                'name' => $bank->name,
                'accountLabel' => $bank->account_label,
                'isActive' => $bank->is_active,
                'logoUrl' => $bank->institution?->logo_url,
                'institutionId' => $bank->bank_institution_id,
            ]),
            'institutions' => $institutions,
            'bankBalances' => $plan && $plan->hasLockedIncomePeriod()
                ? $this->fundBalanceService->bankBalancesForTeam($current_team, $plan)
                : [],
        ]);
    }

    public function store(SaveBankRequest $request, Team $current_team): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['bank_institution_id']) && empty($data['name'])) {
            $institution = BankInstitution::query()->find($data['bank_institution_id']);
            $data['name'] = $institution?->name ?? __('Bank account');
        }

        $current_team->banks()->create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank added.')]);

        return back();
    }

    public function update(SaveBankRequest $request, Team $current_team, Bank $bank): RedirectResponse
    {
        abort_if($bank->team_id !== $current_team->id, 404);

        $bank->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank updated.')]);

        return back();
    }

    public function destroy(Request $request, Team $current_team, Bank $bank): RedirectResponse
    {
        abort_if($bank->team_id !== $current_team->id, 404);

        $bank->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank removed.')]);

        return back();
    }
}
