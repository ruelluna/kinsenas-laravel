<?php

namespace App\Http\Controllers\Api\V1\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveBankRequest;
use App\Models\Bank;
use App\Models\BankInstitution;
use App\Models\Team;
use App\Services\Marketing\BankGhlTagService;
use App\Services\Savings\BankAccountSetupService;
use App\Services\Savings\BankPayloadMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function __construct(
        private BankAccountSetupService $bankAccountSetupService,
        private BankGhlTagService $bankGhlTagService,
    ) {}

    public function index(Team $team): JsonResponse
    {
        return response()->json([
            'data' => $team->banks()->with('institution')->orderBy('sort_order')->get()
                ->map(fn (Bank $bank) => BankPayloadMapper::toOption($bank))->values(),
        ]);
    }

    public function store(SaveBankRequest $request, Team $team): JsonResponse
    {
        $data = $request->validated();
        $institution = ! empty($data['bank_institution_id'])
            ? BankInstitution::query()->findOrFail($data['bank_institution_id'])
            : null;
        $isFirstBankOnTeam = ! $team->banks()->exists();
        $isFirstInstitutionOnTeam = $institution !== null
            && ! $team->banks()->where('bank_institution_id', $institution->id)->exists();

        if ($institution?->supportsSavingsSpaces()) {
            $this->bankAccountSetupService->createSavingsSpaces(
                $team,
                $institution,
                $data['main_label'] ?? $institution->savingsSpacesConfig()['main_label'] ?? 'Main account',
                $data['spaces'] ?? [],
            );
            $this->bankGhlTagService->syncBankAdded(
                $request->user(),
                $team->fresh(),
                $institution,
                withSavingsSpaces: true,
                isFirstBankOnTeam: $isFirstBankOnTeam,
                isFirstInstitutionOnTeam: $isFirstInstitutionOnTeam,
            );

            return response()->json([
                'data' => $team->fresh()->banks()->with('institution')->orderBy('sort_order')->get()
                    ->map(fn (Bank $bank) => BankPayloadMapper::toOption($bank))->values(),
            ], 201);
        }

        $bank = $team->banks()->create(collect([
            ...$data,
            'name' => $data['name'] ?? $institution?->name,
        ])->only(['bank_institution_id', 'name', 'account_label', 'is_active', 'sort_order'])->all());

        if ($institution !== null) {
            $this->bankGhlTagService->syncBankAdded(
                $request->user(),
                $team->fresh(),
                $institution,
                isFirstBankOnTeam: $isFirstBankOnTeam,
                isFirstInstitutionOnTeam: $isFirstInstitutionOnTeam,
            );
        }

        return response()->json(['data' => BankPayloadMapper::toOption($bank->load('institution'))], 201);
    }

    public function update(SaveBankRequest $request, Team $team, Bank $bank): JsonResponse
    {
        abort_if($bank->team_id !== $team->id, 404);

        $bank->update(collect($request->validated())->only([
            'name', 'account_label', 'is_active', 'sort_order',
        ])->all());

        return response()->json(['data' => BankPayloadMapper::toOption($bank->fresh('institution'))]);
    }

    public function destroy(Request $request, Team $team, Bank $bank): JsonResponse
    {
        abort_if($bank->team_id !== $team->id, 404);

        $bank->load('institution');
        $institution = $bank->institution;
        $bank->delete();

        if ($institution !== null) {
            $this->bankGhlTagService->syncBankRemoved($request->user(), $team->fresh(), $institution);
        }

        return response()->noContent();
    }
}
