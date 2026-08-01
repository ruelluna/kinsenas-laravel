<?php

namespace App\Listeners;

use App\Services\Billing\BetaApplicationService;
use Illuminate\Auth\Events\Verified;

class GrantBetaLaunchDiscountOnVerified
{
    public function __construct(private BetaApplicationService $betaApplicationService) {}

    public function handle(Verified $event): void
    {
        $this->betaApplicationService->grantLaunchDiscountIfEligible($event->user);
    }
}
