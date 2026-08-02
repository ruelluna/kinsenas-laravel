<?php

namespace App\Services\Users;

use App\Models\Team;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use App\Services\Marketing\GhlUserTagService;
use App\Support\Marketing\GhlTagCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserDeletionService
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private GhlUserTagService $ghlUserTagService,
    ) {}

    public function deleteBlockReason(User $actor, User $target): ?string
    {
        if ($target->is($actor)) {
            return __('You cannot delete your own account from admin.');
        }

        if ($target->isPlatformAdmin()) {
            $adminCount = User::query()->where('is_platform_admin', true)->count();

            if ($adminCount <= 1) {
                return __('At least one platform admin must remain.');
            }
        }

        $blockingTeam = $this->firstOwnedTeamWithOtherMembers($target);

        if ($blockingTeam !== null) {
            return __('Transfer ownership or remove other members from “:team” before deleting this user.', [
                'team' => $blockingTeam->name,
            ]);
        }

        return null;
    }

    public function delete(User $actor, User $target): void
    {
        if ($reason = $this->deleteBlockReason($actor, $target)) {
            throw ValidationException::withMessages([
                'email' => $reason,
            ]);
        }

        DB::transaction(function () use ($target) {
            $target->ownedTeams()
                ->with('subscription')
                ->get()
                ->each(fn (Team $team) => $this->deleteOwnedTeam($team, $target));

            $this->ghlUserTagService->dispatch(
                $target,
                tagsToAdd: [],
                tagsToRemove: GhlTagCatalog::allStaticTags(),
                context: ['event' => 'account_deleted'],
            );

            DB::table('sessions')->where('user_id', $target->id)->delete();

            $target->delete();

            Log::info('User deleted by platform admin', [
                'user_id' => $target->id,
                'email' => $target->email,
            ]);
        });
    }

    private function deleteOwnedTeam(Team $team, User $owner): void
    {
        if ($team->subscription) {
            $this->subscriptionService->cancel(
                $team->subscription,
                'Admin deleted user account',
            );
        }

        User::query()
            ->where('current_team_id', $team->id)
            ->where('id', '!=', $owner->id)
            ->each(function (User $affectedUser) {
                $fallbackTeam = $affectedUser->personalTeam() ?? $affectedUser->fallbackTeam();

                if ($fallbackTeam) {
                    $affectedUser->switchTeam($fallbackTeam);
                }
            });

        $team->invitations()->delete();
        $team->memberships()->delete();
        $team->delete();
    }

    private function firstOwnedTeamWithOtherMembers(User $target): ?Team
    {
        return $target->ownedTeams()
            ->where('is_personal', false)
            ->withCount([
                'memberships as other_members_count' => fn ($query) => $query->where('user_id', '!=', $target->id),
            ])
            ->get()
            ->first(fn (Team $team) => $team->other_members_count > 0);
    }
}
