<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Services\Dashboard\DashboardSummaryService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, Team $team, DashboardSummaryService $dashboardSummary)
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

        return new DashboardResource([
            ...$dashboardSummary->forTeam($team, $user),
            'pendingInvitations' => $pendingInvitations,
        ]);
    }
}
