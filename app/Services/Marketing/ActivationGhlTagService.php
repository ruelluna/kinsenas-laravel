<?php

namespace App\Services\Marketing;

use App\Models\Team;
use App\Models\User;
use App\Support\Marketing\ActivationGhlTagGuard;
use App\Support\Marketing\GhlTagCatalog;

class ActivationGhlTagService
{
    public function __construct(
        private GhlUserTagService $ghlUserTagService,
        private ActivationGhlTagGuard $activationGhlTagGuard,
    ) {}

    public function syncPlanCreated(User $user, Team $team, string $planTypeSlug): void
    {
        if (! $this->activationGhlTagGuard->isFirstPlanForTeam($team)) {
            return;
        }

        $chosenTag = GhlTagCatalog::planChosenTagForTemplateSlug($planTypeSlug);

        $tagsToAdd = [GhlTagCatalog::PLAN_CREATED];

        if ($chosenTag !== null) {
            $tagsToAdd[] = $chosenTag;
        }

        $tagsToRemove = $chosenTag !== null
            ? GhlTagCatalog::siblingPlanChosenTags($chosenTag)
            : [];

        $this->ghlUserTagService->dispatch(
            $user,
            $tagsToAdd,
            $tagsToRemove,
            [
                'event' => 'plan_created',
                'plan_type' => $planTypeSlug,
                'team_id' => $team->id,
            ],
        );
    }

    public function syncFirstIncomeEntered(User $user, Team $team): void
    {
        if (! $this->activationGhlTagGuard->isFirstIncomePeriodForTeam($team)) {
            return;
        }

        $this->ghlUserTagService->dispatch(
            $user,
            [GhlTagCatalog::FIRST_INCOME_ENTERED],
            [],
            ['event' => 'first_income_entered', 'team_id' => $team->id],
        );
    }

    public function syncIncomeLocked(User $user, Team $team): void
    {
        if (! $this->activationGhlTagGuard->isFirstLockedIncomeForTeam($team)) {
            return;
        }

        $this->ghlUserTagService->dispatch(
            $user,
            [GhlTagCatalog::INCOME_LOCKED, GhlTagCatalog::ACTIVATED_USER],
            [],
            ['event' => 'income_locked', 'team_id' => $team->id],
        );
    }

    public function syncFirstTransfer(User $user, Team $team): void
    {
        if (! $this->activationGhlTagGuard->isFirstTransferForTeam($team)) {
            return;
        }

        $this->ghlUserTagService->dispatch(
            $user,
            [GhlTagCatalog::FIRST_TRANSFER],
            [],
            ['event' => 'first_transfer', 'team_id' => $team->id],
        );
    }

    public function syncFirstSpend(User $user, Team $team): void
    {
        if (! $this->activationGhlTagGuard->isFirstSpendForTeam($team)) {
            return;
        }

        $this->ghlUserTagService->dispatch(
            $user,
            [GhlTagCatalog::FIRST_SPEND],
            [],
            ['event' => 'first_spend', 'team_id' => $team->id],
        );
    }

    public function syncVaultUnlocked(User $user): void
    {
        $this->ghlUserTagService->dispatch(
            $user,
            [GhlTagCatalog::VAULT_UNLOCKED],
            [],
            ['event' => 'vault_unlocked'],
        );
    }
}
