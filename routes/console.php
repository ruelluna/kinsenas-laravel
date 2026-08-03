<?php

use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    TeamInvitation::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->delete();
})->daily()->description('Delete expired team invitations');

Schedule::command('billing:sync-subscription-status')->daily();
Schedule::command('notifications:pending-actions-reminder')->daily();
Schedule::command('notifications:low-fund-balance')->daily();
Schedule::command('notifications:trial-ending-reminder')->daily();
Schedule::command('notifications:income-reminder')->daily();
