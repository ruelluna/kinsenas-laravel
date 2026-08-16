<?php

namespace App\Services\Content;

use App\Enums\BillingMode;
use App\Models\User;
use App\Services\Billing\SubscriptionService;

class LearnAccessService
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function userHasFullLearnAccess(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->isPlatformAdmin()) {
            return true;
        }

        if (BillingMode::isOpenBeta()) {
            return true;
        }

        foreach ($user->teams as $team) {
            if ($this->subscriptionService->teamHasAccess($team)) {
                return true;
            }
        }

        return false;
    }
}
