<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePlatformAdminRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminPlatformUserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $adminFilter = $request->query('admin');

        $users = User::query()
            ->with(['currentTeam.subscription'])
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($adminFilter === 'yes', fn ($query) => $query->where('is_platform_admin', true))
            ->when($adminFilter === 'no', fn ($query) => $query->where('is_platform_admin', false))
            ->orderByDesc('is_platform_admin')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/platform-users/index', [
            'users' => $users->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'isPlatformAdmin' => $user->isPlatformAdmin(),
                'subscriptionStatus' => $user->currentTeam?->subscription?->status->value,
                'subscriptionStatusLabel' => $user->currentTeam?->subscription?->status?->label(),
            ]),
            'filters' => [
                'search' => $search,
                'admin' => $adminFilter,
            ],
            'currentUserId' => $request->user()->id,
            'platformAdminCount' => User::query()->where('is_platform_admin', true)->count(),
        ]);
    }

    public function update(UpdatePlatformAdminRequest $request, User $user): RedirectResponse
    {
        $wantsAdmin = $request->boolean('is_platform_admin');
        $actor = $request->user();

        if (! $wantsAdmin && $user->is($actor)) {
            throw ValidationException::withMessages([
                'is_platform_admin' => __('You cannot revoke your own platform admin access.'),
            ]);
        }

        if (! $wantsAdmin && $user->isPlatformAdmin()) {
            $adminCount = User::query()->where('is_platform_admin', true)->count();

            if ($adminCount <= 1) {
                throw ValidationException::withMessages([
                    'is_platform_admin' => __('At least one platform admin must remain.'),
                ]);
            }
        }

        $user->update(['is_platform_admin' => $wantsAdmin]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $wantsAdmin
                ? __('Platform admin access granted.')
                : __('Platform admin access revoked.'),
        ]);

        return back();
    }
}
