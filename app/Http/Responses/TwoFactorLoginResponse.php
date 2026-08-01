<?php

namespace App\Http\Responses;

use App\Actions\Fortify\UnlockUserVault;
use App\Http\Responses\Concerns\RedirectsToCurrentTeam;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    use RedirectsToCurrentTeam;

    public function __construct(private UnlockUserVault $unlockUserVault) {}

    public function toResponse($request): Response
    {
        if ($request->user() !== null) {
            $this->unlockUserVault->unlockForUser($request, $request->user());
        }

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 200)
            : redirect()->intended($this->redirectPathAfterAuth($request, Fortify::redirects('login')));
    }
}
