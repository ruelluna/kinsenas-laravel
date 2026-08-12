<?php

namespace App\Services\Teams;

use App\Data\TeamInviteReadiness;
use App\Models\Team;
use App\Models\User;
use App\Services\Savings\SavingsPlanService;

class TeamSetupService
{
    public function __construct(private SavingsPlanService $planService) {}

    public function isReadyForInvites(Team $team, User $user): bool
    {
        return $this->readinessForInvites($team, $user)->ready;
    }

    public function readinessForInvites(Team $team, User $user): TeamInviteReadiness
    {
        $steps = $this->inviteSetupSteps($team, $user);
        $ready = collect($steps)->every(fn (array $step) => $step['complete']);

        return new TeamInviteReadiness($ready, $steps);
    }

    /**
     * @return array<int, array{key: string, label: string, complete: bool, href: string}>
     */
    public function dashboardSetupSteps(Team $team, User $user, bool $hasSpending): array
    {
        $inviteSteps = $this->inviteSetupSteps($team, $user);

        return [
            ...$inviteSteps,
            [
                'key' => 'spending',
                'label' => 'Record spending',
                'complete' => $hasSpending,
                'href' => "/{$team->slug}/savings/spending",
            ],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, complete: bool, href: string}>
     */
    private function inviteSetupSteps(Team $team, User $user): array
    {
        $plan = $this->planService->forTeam($team, $user);
        $savingsBase = "/{$team->slug}/savings";

        $hasBank = $team->banks()->exists();
        $hasPlan = $plan !== null;
        $hasIncome = $plan?->hasIncomePeriod() ?? false;

        return [
            [
                'key' => 'bank',
                'label' => 'Add your banks',
                'complete' => $hasBank,
                'href' => "{$savingsBase}/banks",
            ],
            [
                'key' => 'plan',
                'label' => 'Choose a savings plan',
                'complete' => $hasPlan,
                'href' => "{$savingsBase}/plan",
            ],
            [
                'key' => 'income',
                'label' => 'Add income',
                'complete' => $hasIncome,
                'href' => "{$savingsBase}/income",
            ],
        ];
    }
}
