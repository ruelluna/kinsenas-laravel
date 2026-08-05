<?php

namespace App\Services\Marketing;

use App\Models\BankInstitution;
use App\Models\Team;
use App\Models\User;
use App\Support\Marketing\BankGhlTagResolver;

class BankGhlTagService
{
    public function __construct(
        private GhlUserTagService $ghlUserTagService,
        private BankGhlTagResolver $bankGhlTagResolver,
    ) {}

    public function syncBankAdded(
        User $user,
        Team $team,
        BankInstitution $institution,
        ?string $accountLabel = null,
        bool $isFirstBankOnTeam = false,
        bool $isFirstInstitutionOnTeam = false,
    ): void {
        $tagsToAdd = $this->bankGhlTagResolver->tagsToAddOnBankCreated(
            $institution,
            $accountLabel,
            $isFirstBankOnTeam,
            $isFirstInstitutionOnTeam,
        );

        $this->ghlUserTagService->dispatch(
            $user,
            $tagsToAdd,
            [],
            [
                'event' => 'bank_added',
                'team_id' => $team->id,
                'institution_slug' => $institution->slug,
            ],
        );
    }

    public function syncBankRemoved(User $user, Team $team, BankInstitution $institution): void
    {
        $tagsToRemove = $this->bankGhlTagResolver->tagsToRemoveOnBankDeleted($team, $institution);

        $this->ghlUserTagService->dispatch(
            $user,
            [],
            $tagsToRemove,
            [
                'event' => 'bank_removed',
                'team_id' => $team->id,
                'institution_slug' => $institution->slug,
            ],
        );
    }
}
