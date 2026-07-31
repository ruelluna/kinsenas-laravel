<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Services\Dashboard\DashboardSummaryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private DashboardSummaryService $dashboardSummary) {}

    public function __invoke(Request $request, Team $current_team): Response
    {
        $user = $request->user();
        $email = strtolower($user->email);

        $pendingInvitations = TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $invitation) => [
                'code' => $invitation->code,
                'inviterName' => $invitation->inviter->name,
                'team' => [
                    'name' => $invitation->team->name,
                    'slug' => $invitation->team->slug,
                ],
            ]);

        return Inertia::render('dashboard', [
            ...$this->dashboardSummary->forTeam($current_team, $user),
            'pendingInvitations' => $pendingInvitations,
        ]);
    }
}
