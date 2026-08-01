<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BetaApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Billing\BetaApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminBetaApplicationController extends Controller
{
    public function __construct(private BetaApplicationService $betaApplicationService)
    {
    }

    public function index(Request $request): Response
    {
        $status = $request->query('status', BetaApplicationStatus::Pending->value);
        $search = $request->query('search');

        $applications = User::query()
            ->whereNotNull('beta_application_status')
            ->when($status !== '' && $status !== 'all', fn ($query) => $query->where('beta_application_status', $status))
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('beta_enrolled_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/beta-applications/index', [
            'applications' => $applications->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->beta_application_status?->value,
                'statusLabel' => $user->beta_application_status?->label(),
                'emailVerified' => $user->hasVerifiedEmail(),
                'appliedAt' => $user->beta_enrolled_at?->toISOString(),
                'approvedAt' => $user->beta_approved_at?->toISOString(),
            ]),
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'statusOptions' => [
                ['value' => 'all', 'label' => 'All'],
                ...BetaApplicationStatus::filterOptions(),
            ],
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $this->betaApplicationService->approve($user, $request->user());

        return back()->with('success', __('Beta application approved for :name.', ['name' => $user->name]));
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $this->betaApplicationService->reject($user, $request->user());

        return back()->with('success', __('Beta application rejected for :name.', ['name' => $user->name]));
    }
}
