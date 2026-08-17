<?php

namespace App\Console\Commands\Users;

use App\Models\Team;
use App\Models\User;
use App\Services\Users\FinanceActivityScoreService;
use Illuminate\Console\Command;

class RefreshFinanceActivityScoresCommand extends Command
{
    protected $signature = 'users:refresh-finance-activity-scores';

    protected $description = 'Recompute cached finance activity scores for all users and teams';

    public function handle(FinanceActivityScoreService $scoreService): int
    {
        Team::query()->orderBy('id')->each(function (Team $team) use ($scoreService): void {
            $scoreService->refreshTeam($team);
        });

        User::query()->orderBy('id')->each(function (User $user) use ($scoreService): void {
            $scoreService->refreshUser($user);
        });

        $this->info('Finance activity scores refreshed.');

        return self::SUCCESS;
    }
}
