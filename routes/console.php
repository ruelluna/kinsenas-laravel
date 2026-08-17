<?php

use App\Enums\UserActivityAction;
use App\Models\ContentEngagementEvent;
use App\Models\TeamInvitation;
use App\Services\Audit\UserActivityLogger;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $logger = app(UserActivityLogger::class);

    TeamInvitation::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->each(function (TeamInvitation $invitation) use ($logger): void {
            $logger->log(
                UserActivityAction::TeamInvitationExpired,
                'Expired team invitation to :properties.email',
                causer: null,
                subject: $invitation,
                properties: [
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'role_label' => $invitation->role->label(),
                    'invitation_code' => $invitation->code,
                    'source' => 'system',
                ],
                team: $invitation->team,
            );

            $invitation->delete();
        });
})->daily()->description('Delete expired team invitations');

Schedule::command('billing:sync-subscription-status')->daily();
Schedule::command('users:refresh-finance-activity-scores')->daily();
Schedule::command('notifications:pending-actions-reminder')->daily();
Schedule::command('notifications:low-fund-balance')->daily();
Schedule::command('notifications:trial-ending-reminder')->daily();
Schedule::command('notifications:income-reminder')->daily();

Schedule::call(function () {
    ContentEngagementEvent::query()
        ->where('created_at', '<', now()->subDays(365))
        ->delete();
})->daily()->description('Prune old content engagement events');
