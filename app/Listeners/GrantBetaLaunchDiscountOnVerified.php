<?php

namespace App\Listeners;

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

        $tagsToAdd = [
            GhlTagCatalog::KINSENAS_USER,
            GhlTagCatalog::REGISTERED,
            GhlTagCatalog::EMAIL_VERIFIED,
        ];

        if ($user->beta_enrolled_at !== null) {
            $tagsToAdd[] = GhlTagCatalog::KINSENAS_BETA;
        }

        if ($user->beta_enrolled_at !== null && $user->beta_launch_discount_eligible) {
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
