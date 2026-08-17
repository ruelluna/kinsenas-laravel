<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FinanceActivityTier;
use App\Enums\PlatformRole;
use App\Enums\UserActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeletePlatformUserRequest;
use App\Http\Requests\Admin\UpdatePlatformUserRoleRequest;
use App\Models\User;
use App\Services\Audit\UserActivityLogger;
use App\Services\Users\UserDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminPlatformUserController extends Controller
{
    public function __construct(
        private UserDeletionService $userDeletionService,
        private UserActivityLogger $activityLogger,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $roleFilter = $request->query('role');
        $activityTier = $request->query('activity_tier');
        $minScore = $request->query('min_score');

        $users = User::query()
            ->with(['currentTeam.subscription', 'roles'])
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(
                filled($roleFilter),
                fn ($query) => $query->role($roleFilter),
            )
            ->when(
                filled($activityTier),
                fn ($query) => $query->where('finance_activity_tier', $activityTier),
            )
            ->when(
                filled($minScore) && is_numeric($minScore),
                fn ($query) => $query->where('finance_activity_score', '>=', (int) $minScore),
            )
            ->orderByRaw(
                'CASE WHEN EXISTS (
                    SELECT 1 FROM model_has_roles
                    INNER JOIN roles ON roles.id = model_has_roles.role_id
                    WHERE model_has_roles.model_id = users.id
                    AND model_has_roles.model_type = ?
                    AND roles.name = ?
                ) THEN 0 ELSE 1 END',
                [User::class, PlatformRole::PlatformAdmin->value],
            )
            ->orderByDesc('finance_activity_score')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $actor = $request->user();

        return Inertia::render('admin/platform-users/index', [
            'users' => $users->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->platformRole()?->value ?? PlatformRole::User->value,
                'roleLabel' => $user->platformRole()?->label() ?? PlatformRole::User->label(),
                'isPlatformAdmin' => $user->isPlatformAdmin(),
                'subscriptionStatus' => $user->currentTeam?->subscription?->status->value,
                'subscriptionStatusLabel' => $user->currentTeam?->subscription?->status?->label(),
                'financeActivityScore' => $user->finance_activity_score,
                'financeActivityTier' => $user->finance_activity_tier?->value ?? FinanceActivityTier::Inactive->value,
                'financeActivityTierLabel' => $user->finance_activity_tier?->label() ?? FinanceActivityTier::Inactive->label(),
                'lastFinanceActivityAt' => $user->last_finance_activity_at?->toISOString(),
                'deleteBlockReason' => $this->userDeletionService->deleteBlockReason($actor, $user),
            ]),
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
                'activity_tier' => $activityTier,
                'min_score' => $minScore,
            ],
            'currentUserId' => $request->user()->id,
            'platformAdminCount' => User::role(PlatformRole::PlatformAdmin->value)->count(),
            'roleOptions' => PlatformRole::assignable(),
            'activityTierOptions' => FinanceActivityTier::filterOptions(),
        ]);
    }

    public function update(UpdatePlatformUserRoleRequest $request, User $user): RedirectResponse
    {
        $role = PlatformRole::from($request->string('role')->toString());
        $actor = $request->user();

        if ($role !== PlatformRole::PlatformAdmin && $user->is($actor)) {
            throw ValidationException::withMessages([
                'role' => __('You cannot change your own platform role.'),
            ]);
        }

        if ($user->isPlatformAdmin() && $role !== PlatformRole::PlatformAdmin) {
            $adminCount = User::role(PlatformRole::PlatformAdmin->value)->count();

            if ($adminCount <= 1) {
                throw ValidationException::withMessages([
                    'role' => __('At least one platform admin must remain.'),
                ]);
            }
        }

        $previousRole = $user->platformRole();
        $user->syncPlatformRole($role);

        $this->activityLogger->log(
            UserActivityAction::AdminPlatformUserUpdated,
            'Changed platform role for :properties.user_name from :properties.previous_role to :properties.role',
            $actor,
            $user,
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'previous_role' => $previousRole?->value,
                'role' => $role->value,
            ],
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Platform role updated.'),
        ]);

        return back();
    }

    public function destroy(DeletePlatformUserRequest $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        $this->activityLogger->log(
            UserActivityAction::AdminPlatformUserDeleted,
            'Removed platform user :properties.user_name',
            $actor,
            properties: [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
            ],
        );

        $this->userDeletionService->delete($actor, $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('User account removed.'),
        ]);

        return to_route('admin.platform-users.index');
    }
}
