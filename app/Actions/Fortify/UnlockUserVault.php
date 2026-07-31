<?php

namespace App\Actions\Fortify;

use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;

class UnlockUserVault
{
    public function __construct(
        private FinancialEncryptionService $encryption,
        private VaultKeyManager $vaultKeyManager,
    ) {
    }

    public function handle(Request $request, callable $next): mixed
    {
        $response = $next($request);

        $user = $request->user();
        $password = $request->input(Fortify::password());

        if ($user !== null && is_string($password) && $password !== '' && $user->vault !== null && ! $user->vault->is_locked) {
            try {
                $dek = $this->encryption->unlockWithPassword($user->vault, $password);
                $this->vaultKeyManager->storeUserDek($dek);
            } catch (\Throwable) {
                //
            }
        }

        return $response;
    }
}
