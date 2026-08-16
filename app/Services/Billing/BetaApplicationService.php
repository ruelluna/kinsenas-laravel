<?php

namespace App\Services\Billing;

use App\Enums\BillingMode;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class BetaApplicationService
{
    public function enroll(User $user): void
    {
        if (! BillingMode::isOpenBeta()) {
            return;
        }

        if ($user->beta_enrolled_at !== null) {
            return;
        }

        $user->forceFill([
            'beta_enrolled_at' => now(),
            'beta_launch_discount_eligible' => true,
        ])->save();

        Log::info('Beta participant enrolled', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function grantLaunchDiscountIfEligible(User $user): void
    {
        if (! BillingMode::isOpenBeta()) {
            return;
        }

        if ($user->beta_enrolled_at === null || $user->beta_launch_discount_eligible) {
            return;
        }

        $user->forceFill(['beta_launch_discount_eligible' => true])->save();
    }

    /**
     * @return array{
     *     isActive: bool,
     *     launchDiscountPercent: int,
     *     isParticipant: bool,
     *     launchDiscountEligible: bool,
     *     enrolledAt: string|null
     * }
     */
    public function sharedProps(?User $user): array
    {
        return [
            'isActive' => BillingMode::isOpenBeta(),
            'launchDiscountPercent' => (int) config('billing.open_beta.launch_discount_percent', 20),
            'isParticipant' => $user?->beta_enrolled_at !== null,
            'launchDiscountEligible' => (bool) ($user?->beta_launch_discount_eligible ?? false),
            'enrolledAt' => $user?->beta_enrolled_at instanceof Carbon
                ? $user->beta_enrolled_at->toISOString()
                : null,
        ];
    }
}
