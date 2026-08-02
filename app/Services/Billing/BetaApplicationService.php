<?php

namespace App\Services\Billing;

use App\Enums\BetaApplicationStatus;
use App\Enums\BillingMode;
use App\Jobs\SyncBetaApplicationToGhl;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BetaApplicationService
{
    public function __construct(private BetaAccessCodeService $betaAccessCodeService) {}

    public function apply(User $user): void
    {
        if (! BillingMode::isOpenBeta()) {
            return;
        }

        if ($user->beta_application_status !== null) {
            return;
        }

        $user->forceFill([
            'beta_enrolled_at' => now(),
            'beta_application_status' => BetaApplicationStatus::Pending,
        ])->save();

        SyncBetaApplicationToGhl::dispatch($user, 'application_submitted')->afterCommit();

        Log::info('Beta application submitted', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function applyWithOptionalCode(User $user, ?string $code): void
    {
        if (! BillingMode::isOpenBeta()) {
            return;
        }

        $normalizedCode = is_string($code) ? trim($code) : '';

        if ($normalizedCode === '') {
            $this->apply($user);

            return;
        }

        $accessCode = $this->betaAccessCodeService->findRedeemable($normalizedCode);

        if ($accessCode === null) {
            throw ValidationException::withMessages([
                'beta_code' => __('This beta access code is invalid or no longer available.'),
            ]);
        }

        if ($user->beta_application_status !== null) {
            return;
        }

        $user->forceFill([
            'beta_enrolled_at' => now(),
            'beta_application_status' => BetaApplicationStatus::Pending,
        ])->save();

        $this->betaAccessCodeService->redeem($accessCode, $user);
        $this->approveViaCode($user);

        Log::info('Beta application auto-approved via access code', [
            'user_id' => $user->id,
            'email' => $user->email,
            'beta_access_code_id' => $user->beta_access_code_id,
        ]);
    }

    public function approve(User $user, User $admin): void
    {
        if ($user->beta_application_status === BetaApplicationStatus::Approved) {
            return;
        }

        $user->forceFill([
            'beta_application_status' => BetaApplicationStatus::Approved,
            'beta_approved_at' => now(),
            'beta_approved_by' => $admin->id,
        ])->save();

        $this->grantLaunchDiscountIfEligible($user);

        SyncBetaApplicationToGhl::dispatch($user->fresh(), 'application_approved')->afterCommit();

        Log::info('Beta application approved', [
            'user_id' => $user->id,
            'admin_user_id' => $admin->id,
        ]);
    }

    public function approveViaCode(User $user): void
    {
        if ($user->beta_application_status === BetaApplicationStatus::Approved) {
            return;
        }

        $user->forceFill([
            'beta_application_status' => BetaApplicationStatus::Approved,
            'beta_approved_at' => now(),
            'beta_approved_by' => null,
        ])->save();

        $this->grantLaunchDiscountIfEligible($user);

        SyncBetaApplicationToGhl::dispatch($user->fresh(), 'application_approved_via_code')->afterCommit();
    }

    public function reject(User $user, User $admin): void
    {
        if ($user->beta_application_status === BetaApplicationStatus::Rejected) {
            return;
        }

        $user->forceFill([
            'beta_application_status' => BetaApplicationStatus::Rejected,
            'beta_approved_at' => null,
            'beta_approved_by' => $admin->id,
        ])->save();

        SyncBetaApplicationToGhl::dispatch($user->fresh(), 'application_rejected')->afterCommit();

        Log::info('Beta application rejected', [
            'user_id' => $user->id,
            'admin_user_id' => $admin->id,
        ]);
    }

    public function hasAppAccess(User $user): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        if (! BillingMode::isOpenBeta()) {
            return true;
        }

        return $user->beta_application_status === BetaApplicationStatus::Approved;
    }

    public function grantLaunchDiscountIfEligible(User $user): void
    {
        if (! BillingMode::isOpenBeta()) {
            return;
        }

        if ($user->beta_application_status !== BetaApplicationStatus::Approved
            || $user->beta_launch_discount_eligible) {
            return;
        }

        $user->forceFill(['beta_launch_discount_eligible' => true])->save();
    }

    /**
     * @return array{
     *     isActive: bool,
     *     launchDiscountPercent: int,
     *     applicationStatus: string|null,
     *     applicationStatusLabel: string|null,
     *     isParticipant: bool,
     *     isApproved: bool,
     *     isPending: bool,
     *     launchDiscountEligible: bool,
     *     appliedAt: string|null,
     *     approvedAt: string|null
     * }
     */
    public function sharedProps(?User $user): array
    {
        $status = $user?->beta_application_status;

        return [
            'isActive' => BillingMode::isOpenBeta(),
            'launchDiscountPercent' => (int) config('billing.open_beta.launch_discount_percent', 20),
            'applicationStatus' => $status instanceof BetaApplicationStatus ? $status->value : $status,
            'applicationStatusLabel' => $status instanceof BetaApplicationStatus ? $status->label() : null,
            'isParticipant' => $user?->beta_enrolled_at !== null,
            'isApproved' => $status === BetaApplicationStatus::Approved,
            'isPending' => $status === BetaApplicationStatus::Pending,
            'launchDiscountEligible' => (bool) ($user?->beta_launch_discount_eligible ?? false),
            'appliedAt' => $user?->beta_enrolled_at instanceof Carbon
                ? $user->beta_enrolled_at->toISOString()
                : null,
            'approvedAt' => $user?->beta_approved_at instanceof Carbon
                ? $user->beta_approved_at->toISOString()
                : null,
        ];
    }
}
