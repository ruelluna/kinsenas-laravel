<?php

namespace App\Listeners;

use App\Enums\BetaApplicationStatus;
use App\Services\Billing\BetaApplicationService;
use App\Services\Marketing\GhlUserTagService;
use App\Support\Marketing\GhlTagCatalog;
use Illuminate\Auth\Events\Verified;

class GrantBetaLaunchDiscountOnVerified
{
    public function __construct(
        private BetaApplicationService $betaApplicationService,
        private GhlUserTagService $ghlUserTagService,
    ) {}

    public function handle(Verified $event): void
    {
        $user = $event->user;

        $this->betaApplicationService->grantLaunchDiscountIfEligible($user);

        $user = $user->fresh();

        $tagsToAdd = [GhlTagCatalog::EMAIL_VERIFIED];

        if ($user->beta_application_status === BetaApplicationStatus::Approved
            && $user->beta_launch_discount_eligible) {
            $tagsToAdd[] = GhlTagCatalog::BETA_LAUNCH_DISCOUNT_ELIGIBLE;
        }

        $this->ghlUserTagService->dispatch(
            $user,
            $tagsToAdd,
            [],
            ['event' => 'email_verified'],
        );
    }
}
