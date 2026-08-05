<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\BillingMode;
use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use App\Services\Billing\BetaAccessCodeService;
use App\Services\Billing\BillingPlanPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class RegisterContextController extends Controller
{
    public function __invoke(Request $request, BillingPlanPresenter $billingPlanPresenter): JsonResponse
    {
        return response()->json([
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            'teamInvitation' => $this->teamInvitation($request),
            'trialOffer' => BillingMode::isOpenBeta() ? null : $billingPlanPresenter->trialOffer(),
            'openBetaOffer' => $billingPlanPresenter->openBetaOffer(),
            ...$this->betaAccessCodeContext($request),
        ]);
    }

    /**
     * @return array{code: string, teamName: string}|null
     */
    private function teamInvitation(Request $request): ?array
    {
        $invitationCode = $request->query('invitation');

        if (! is_string($invitationCode)) {
            return null;
        }

        $invitation = TeamInvitation::query()
            ->with('team')
            ->where('code', $invitationCode)
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->first();

        if (! $invitation) {
            return null;
        }

        return [
            'code' => $invitation->code,
            'teamName' => $invitation->team->name,
        ];
    }

    /**
     * @return array{betaCode: string|null, betaCodeLabel: string|null}
     */
    private function betaAccessCodeContext(Request $request): array
    {
        if (! BillingMode::isOpenBeta()) {
            return [
                'betaCode' => null,
                'betaCodeLabel' => null,
            ];
        }

        $betaCode = $request->query('beta_code');

        if (! is_string($betaCode) || trim($betaCode) === '') {
            return [
                'betaCode' => null,
                'betaCodeLabel' => null,
            ];
        }

        $accessCode = app(BetaAccessCodeService::class)->findRedeemable($betaCode);

        return [
            'betaCode' => trim($betaCode),
            'betaCodeLabel' => $accessCode?->label,
        ];
    }
}
