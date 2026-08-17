<?php

namespace App\Observers;

use App\Models\Bank;
use App\Models\FundAddedEntry;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\IncomePeriod;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\User;
use App\Models\UserVault;
use App\Services\Users\FinanceActivityScoreService;
use Illuminate\Database\Eloquent\Model;

class FinanceActivityScoreObserver
{
    public function __construct(private FinanceActivityScoreService $scoreService) {}

    public function saved(Model $model): void
    {
        $this->refreshFromModel($model);
    }

    public function deleted(Model $model): void
    {
        $this->refreshFromModel($model);
    }

    private function refreshFromModel(Model $model): void
    {
        $team = $this->resolveTeam($model);

        if ($team instanceof Team) {
            $this->scoreService->refreshTeam($team);

            $owner = $team->owner();

            if ($owner instanceof User) {
                $this->scoreService->refreshUser($owner);
            }
        }

        foreach ($this->resolveActorUsers($model) as $user) {
            $this->scoreService->refreshUser($user);
        }
    }

    private function resolveTeam(Model $model): ?Team
    {
        return match (true) {
            $model instanceof Team => $model,
            $model instanceof Bank => $model->team,
            $model instanceof SavingsPlan => $model->team,
            $model instanceof IncomePeriod => $model->plan?->team,
            $model instanceof FundSpend => $model->plan?->team,
            $model instanceof FundTransfer => $model->plan?->team,
            $model instanceof FundAddedEntry => $model->plan?->team,
            $model instanceof UserVault => $model->user?->personalTeam(),
            default => null,
        };
    }

    /**
     * @return array<int, User>
     */
    private function resolveActorUsers(Model $model): array
    {
        $users = [];

        if ($model instanceof UserVault && $model->user instanceof User) {
            $users[] = $model->user;
        }

        if ($model instanceof SavingsPlan && $model->creator instanceof User) {
            $users[] = $model->creator;
        }

        if ($model instanceof IncomePeriod && $model->lockedBy instanceof User) {
            $users[] = $model->lockedBy;
        }

        if ($model instanceof FundSpend) {
            if ($model->createdBy instanceof User) {
                $users[] = $model->createdBy;
            }

            if ($model->confirmedBy instanceof User) {
                $users[] = $model->confirmedBy;
            }
        }

        if ($model instanceof FundTransfer) {
            if ($model->createdBy instanceof User) {
                $users[] = $model->createdBy;
            }

            if ($model->confirmedBy instanceof User) {
                $users[] = $model->confirmedBy;
            }
        }

        if ($model instanceof FundAddedEntry && $model->createdBy instanceof User) {
            $users[] = $model->createdBy;
        }

        return collect($users)
            ->unique(fn (User $user) => $user->id)
            ->values()
            ->all();
    }
}
