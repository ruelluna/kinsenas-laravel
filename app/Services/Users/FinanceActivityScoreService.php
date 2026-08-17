<?php

namespace App\Services\Users;

use App\Data\FinanceActivitySnapshot;
use App\Enums\FinanceActivityTier;
use App\Enums\TransferStatus;
use App\Models\Bank;
use App\Models\FundAddedEntry;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\IncomePeriod;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FinanceActivityScoreService
{
    private const SETUP_WEIGHTS = [
        'vault_unlocked' => 10,
        'bank' => 10,
        'plan' => 15,
        'income' => 15,
        'income_locked' => 20,
        'transfer' => 15,
        'spend' => 15,
    ];

    public function scoreForTeam(Team $team): FinanceActivitySnapshot
    {
        $owner = $team->owner();

        return $this->buildSnapshot(
            team: $team,
            vaultUser: $owner instanceof User ? $owner : null,
            frequencyUser: null,
        );
    }

    public function scoreForUser(User $user): FinanceActivitySnapshot
    {
        $team = $user->currentTeam ?? $user->personalTeam();

        if ($team === null) {
            return $this->emptySnapshot();
        }

        return $this->buildSnapshot(
            team: $team,
            vaultUser: $user,
            frequencyUser: $user,
        );
    }

    public function refreshTeam(Team $team): FinanceActivitySnapshot
    {
        $snapshot = $this->scoreForTeam($team);

        $team->forceFill([
            'finance_activity_score' => $snapshot->score,
            'finance_activity_tier' => $snapshot->tier->value,
            'last_finance_activity_at' => $snapshot->lastFinanceActivityAt,
        ])->saveQuietly();

        return $snapshot;
    }

    public function refreshUser(User $user): FinanceActivitySnapshot
    {
        $snapshot = $this->scoreForUser($user);

        $user->forceFill([
            'finance_activity_score' => $snapshot->score,
            'finance_activity_tier' => $snapshot->tier->value,
            'last_finance_activity_at' => $snapshot->lastFinanceActivityAt,
        ])->saveQuietly();

        return $snapshot;
    }

    public static function resolveTier(int $score, bool $incomeLocked): FinanceActivityTier
    {
        if ($incomeLocked || $score >= 80) {
            return FinanceActivityTier::Activated;
        }

        if ($score >= 60) {
            return FinanceActivityTier::Active;
        }

        if ($score >= 30) {
            return FinanceActivityTier::Partial;
        }

        return FinanceActivityTier::Inactive;
    }

    private function buildSnapshot(
        Team $team,
        ?User $vaultUser,
        ?User $frequencyUser,
    ): FinanceActivitySnapshot {
        $incomeLocked = $this->teamHasLockedIncome($team);
        $setupPoints = $this->setupPoints($team, $vaultUser);
        $lastActivityAt = $this->lastFinanceActivityAt($team);
        $recencyPoints = $this->recencyPoints($lastActivityAt);
        $frequencyPoints = $frequencyUser !== null
            ? $this->userFrequencyPoints($team, $frequencyUser)
            : $this->teamFrequencyPoints($team);

        $score = (int) round(
            ($setupPoints * 0.5)
            + ($recencyPoints * 0.25)
            + ($frequencyPoints * 0.25),
        );

        $score = max(0, min(100, $score));

        return new FinanceActivitySnapshot(
            score: $score,
            tier: self::resolveTier($score, $incomeLocked),
            lastFinanceActivityAt: $lastActivityAt,
            breakdown: [
                'setup' => (int) round($setupPoints * 0.5),
                'recency' => (int) round($recencyPoints * 0.25),
                'frequency' => (int) round($frequencyPoints * 0.25),
            ],
            incomeLocked: $incomeLocked,
        );
    }

    private function emptySnapshot(): FinanceActivitySnapshot
    {
        return new FinanceActivitySnapshot(
            score: 0,
            tier: FinanceActivityTier::Inactive,
            lastFinanceActivityAt: null,
            breakdown: [
                'setup' => 0,
                'recency' => 0,
                'frequency' => 0,
            ],
            incomeLocked: false,
        );
    }

    private function setupPoints(Team $team, ?User $vaultUser): int
    {
        $completed = 0;

        if ($this->vaultIsUnlocked($vaultUser)) {
            $completed += self::SETUP_WEIGHTS['vault_unlocked'];
        }

        if ($team->banks()->exists()) {
            $completed += self::SETUP_WEIGHTS['bank'];
        }

        if ($team->savingsPlans()->exists()) {
            $completed += self::SETUP_WEIGHTS['plan'];
        }

        if ($this->teamHasIncome($team)) {
            $completed += self::SETUP_WEIGHTS['income'];
        }

        if ($this->teamHasLockedIncome($team)) {
            $completed += self::SETUP_WEIGHTS['income_locked'];
        }

        if ($this->teamHasTransfer($team)) {
            $completed += self::SETUP_WEIGHTS['transfer'];
        }

        if ($this->teamHasConfirmedSpend($team)) {
            $completed += self::SETUP_WEIGHTS['spend'];
        }

        return $completed;
    }

    private function teamHasFinanceActivity(Team $team): bool
    {
        return $team->banks()->exists()
            || $team->savingsPlans()->exists();
    }

    private function vaultIsUnlocked(?User $user): bool
    {
        return $user?->vault !== null && ! $user->vault->is_locked;
    }

    private function teamHasIncome(Team $team): bool
    {
        return IncomePeriod::query()
            ->whereHas('plan', fn ($query) => $query->where('team_id', $team->id))
            ->exists();
    }

    private function teamHasLockedIncome(Team $team): bool
    {
        return IncomePeriod::query()
            ->where('is_locked', true)
            ->whereHas('plan', fn ($query) => $query->where('team_id', $team->id))
            ->exists();
    }

    private function teamHasTransfer(Team $team): bool
    {
        return FundTransfer::query()
            ->whereHas('plan', fn ($query) => $query->where('team_id', $team->id))
            ->exists();
    }

    private function teamHasConfirmedSpend(Team $team): bool
    {
        return FundSpend::query()
            ->where('status', TransferStatus::Confirmed)
            ->whereHas('plan', fn ($query) => $query->where('team_id', $team->id))
            ->exists();
    }

    private function lastFinanceActivityAt(Team $team): ?CarbonInterface
    {
        $timestamps = collect([
            $this->maxTimestamp(Bank::query()->where('team_id', $team->id)),
            $this->maxTimestamp(SavingsPlan::query()->where('team_id', $team->id)),
            $this->maxPlanRelatedTimestamp(IncomePeriod::query(), $team, ['created_at', 'locked_at']),
            $this->maxPlanRelatedTimestamp(FundSpend::query()->where('status', TransferStatus::Confirmed), $team, ['created_at', 'confirmed_at']),
            $this->maxPlanRelatedTimestamp(FundTransfer::query()->where('status', TransferStatus::Confirmed), $team, ['created_at', 'confirmed_at']),
            $this->maxPlanRelatedTimestamp(FundAddedEntry::query(), $team, ['created_at']),
        ]);

        return $timestamps
            ->filter()
            ->map(fn ($timestamp) => Carbon::parse($timestamp))
            ->max();
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<int, string>  $columns
     */
    private function maxPlanRelatedTimestamp($query, Team $team, array $columns): ?CarbonInterface
    {
        $query->whereHas('plan', fn ($planQuery) => $planQuery->where('team_id', $team->id));

        $maxValues = collect($columns)
            ->map(fn (string $column) => (clone $query)->max($column))
            ->filter();

        if ($maxValues->isEmpty()) {
            return null;
        }

        return Carbon::parse($maxValues->max());
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function maxTimestamp($query): ?CarbonInterface
    {
        $value = $query->max('created_at');

        return $value !== null ? Carbon::parse($value) : null;
    }

    private function recencyPoints(?CarbonInterface $lastActivityAt): int
    {
        if ($lastActivityAt === null) {
            return 0;
        }

        $days = $lastActivityAt->diffInDays(now());

        return match (true) {
            $days <= 7 => 100,
            $days <= 14 => 75,
            $days <= 30 => 50,
            $days <= 60 => 25,
            default => 0,
        };
    }

    private function teamFrequencyPoints(Team $team): int
    {
        return $this->frequencyPercent($this->teamActionCount($team, now()->subDays(30)));
    }

    private function userFrequencyPoints(Team $team, User $user): int
    {
        return $this->frequencyPercent($this->userActionCount($team, $user, now()->subDays(30)));
    }

    private function frequencyPercent(int $actionCount): int
    {
        return match (true) {
            $actionCount === 0 => 0,
            $actionCount <= 2 => 50,
            $actionCount <= 5 => 75,
            default => 100,
        };
    }

    private function teamActionCount(Team $team, CarbonInterface $since): int
    {
        $planIds = SavingsPlan::query()->where('team_id', $team->id)->pluck('id');

        if ($planIds->isEmpty()) {
            return Bank::query()
                ->where('team_id', $team->id)
                ->where('created_at', '>=', $since)
                ->count();
        }

        return Bank::query()
            ->where('team_id', $team->id)
            ->where('created_at', '>=', $since)
            ->count()
            + SavingsPlan::query()
                ->where('team_id', $team->id)
                ->where('created_at', '>=', $since)
                ->count()
            + IncomePeriod::query()
                ->whereIn('plan_id', $planIds)
                ->where('created_at', '>=', $since)
                ->count()
            + FundSpend::query()
                ->whereIn('savings_plan_id', $planIds)
                ->where('status', TransferStatus::Confirmed)
                ->where('confirmed_at', '>=', $since)
                ->count()
            + FundTransfer::query()
                ->whereIn('savings_plan_id', $planIds)
                ->where('status', TransferStatus::Confirmed)
                ->where('confirmed_at', '>=', $since)
                ->count()
            + FundAddedEntry::query()
                ->whereIn('savings_plan_id', $planIds)
                ->where('created_at', '>=', $since)
                ->count();
    }

    private function userActionCount(Team $team, User $user, CarbonInterface $since): int
    {
        $planIds = SavingsPlan::query()->where('team_id', $team->id)->pluck('id');

        $planActions = $planIds->isEmpty()
            ? 0
            : SavingsPlan::query()
                ->where('team_id', $team->id)
                ->where('created_by_user_id', $user->id)
                ->where('created_at', '>=', $since)
                ->count()
            + IncomePeriod::query()
                ->whereIn('plan_id', $planIds)
                ->where('locked_by_user_id', $user->id)
                ->where('locked_at', '>=', $since)
                ->count()
            + FundSpend::query()
                ->whereIn('savings_plan_id', $planIds)
                ->where(function ($query) use ($user) {
                    $query->where('created_by_user_id', $user->id)
                        ->orWhere('confirmed_by_user_id', $user->id);
                })
                ->where(function ($query) use ($since) {
                    $query->where('created_at', '>=', $since)
                        ->orWhere('confirmed_at', '>=', $since);
                })
                ->count()
            + FundTransfer::query()
                ->whereIn('savings_plan_id', $planIds)
                ->where(function ($query) use ($user) {
                    $query->where('created_by_user_id', $user->id)
                        ->orWhere('confirmed_by_user_id', $user->id);
                })
                ->where(function ($query) use ($since) {
                    $query->where('created_at', '>=', $since)
                        ->orWhere('confirmed_at', '>=', $since);
                })
                ->count()
            + FundAddedEntry::query()
                ->whereIn('savings_plan_id', $planIds)
                ->where('created_by_user_id', $user->id)
                ->where('created_at', '>=', $since)
                ->count();

        return $planActions;
    }
}
