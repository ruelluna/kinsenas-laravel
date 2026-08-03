<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveBankRequest;
use App\Models\Bank;
use App\Models\BankInstitution;
use App\Models\Team;
use App\Services\Marketing\BankGhlTagService;
use App\Services\Savings\BankAccountSetupService;
use App\Services\Savings\BankPayloadMapper;
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
        private BankAccountSetupService $bankAccountSetupService,
        private BankGhlTagService $bankGhlTagService,
    ) {}

    public function index(Request $request, Team $current_team): Response
    {
        $plan = $this->planService->forTeam($current_team, $request->user());
        $institutions = BankInstitution::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (BankInstitution $institution) => BankPayloadMapper::toInstitution($institution));

        return Inertia::render('savings/banks/index', [
            'banks' => $current_team->banks()->with('institution')->orderBy('sort_order')->get()->map(
                fn (Bank $bank) => BankPayloadMapper::toOption($bank),
            ),
            'institutions' => $institutions,
            'bankBalances' => $plan && $plan->shouldShowFundBalances()
                ? $this->fundBalanceService->bankBalancesForTeam($current_team, $plan)
                : [],
        ]);
    }

    public function store(SaveBankRequest $request, Team $current_team): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['bank_institution_id'])) {
            $institution = BankInstitution::query()->findOrFail($data['bank_institution_id']);
            $isFirstBankOnTeam = ! $current_team->banks()->exists();
            $isFirstInstitutionOnTeam = ! $current_team->banks()
                ->where('bank_institution_id', $institution->id)
                ->exists();

            if ($institution->supportsSavingsSpaces()) {
                $this->bankAccountSetupService->createSavingsSpaces(
                    $current_team,
                    $institution,
                    $data['main_label'] ?? $institution->savingsSpacesConfig()['main_label'] ?? 'Main account',
                    $data['spaces'] ?? [],
                );

                $this->bankGhlTagService->syncBankAdded(
                    $request->user(),
                    $current_team->fresh(),
                    $institution,
                    withSavingsSpaces: true,
                    isFirstBankOnTeam: $isFirstBankOnTeam,
                    isFirstInstitutionOnTeam: $isFirstInstitutionOnTeam,
                );

                Inertia::flash('toast', ['type' => 'success', 'message' => __('GoTyme account and GoSave spaces added.')]);

                return back();
            }

            if (empty($data['name'])) {
                $data['name'] = $institution->name;
            }
        }

        $isFirstBankOnTeam = ! $current_team->banks()->exists();
        $isFirstInstitutionOnTeam = ! empty($data['bank_institution_id'])
            && ! $current_team->banks()->where('bank_institution_id', $data['bank_institution_id'])->exists();

        $current_team->banks()->create($data);

        if (! empty($data['bank_institution_id'])) {
            $institution = BankInstitution::query()->find($data['bank_institution_id']);

            if ($institution !== null) {
                $this->bankGhlTagService->syncBankAdded(
                    $request->user(),
                    $current_team->fresh(),
                    $institution,
                    isFirstBankOnTeam: $isFirstBankOnTeam,
                    isFirstInstitutionOnTeam: $isFirstInstitutionOnTeam,
                );
            }
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank added.')]);

        return back();
    }

    public function update(SaveBankRequest $request, Team $current_team, Bank $bank): RedirectResponse
    {
        abort_if($bank->team_id !== $current_team->id, 404);

        $bank->update(collect($request->validated())->only([
            'name',
            'account_label',
            'is_active',
            'sort_order',
        ])->all());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank updated.')]);

        return back();
    }

    public function destroy(Request $request, Team $current_team, Bank $bank): RedirectResponse
    {
        abort_if($bank->team_id !== $current_team->id, 404);

        $bank->load('institution');
        $institution = $bank->institution;

        $bank->delete();

        if ($institution !== null) {
            $this->bankGhlTagService->syncBankRemoved(
                $request->user(),
                $current_team->fresh(),
                $institution,
            );
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank removed.')]);

        return back();
    }
}
