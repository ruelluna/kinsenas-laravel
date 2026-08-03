<?php

namespace App\Support\Marketing;

use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\IncomePeriod;
use App\Models\SavingsPlan;
use App\Models\Team;

final class ActivationGhlTagGuard
{
    public function isFirstPlanForTeam(Team $team): bool
    {
        return SavingsPlan::query()->where('team_id', $team->id)->count() === 1;
    }

    public function isFirstIncomePeriodForTeam(Team $team): bool
    {
        return IncomePeriod::query()
            ->whereHas('plan', fn ($query) => $query->where('team_id', $team->id))
            ->count() === 1;
    }

    public function isFirstLockedIncomeForTeam(Team $team): bool
    {
        return $this->isFirstIncomePeriodForTeam($team);
    }

    public function isFirstTransferForTeam(Team $team): bool
    {
        return FundTransfer::query()
            ->whereHas('plan', fn ($query) => $query->where('team_id', $team->id))
            ->count() === 1;
    }

    public function isFirstSpendForTeam(Team $team): bool
    {
        return FundSpend::query()
            ->whereHas('plan', fn ($query) => $query->where('team_id', $team->id))
            ->count() === 1;
    }
}
