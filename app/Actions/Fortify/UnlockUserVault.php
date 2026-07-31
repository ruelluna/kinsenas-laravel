<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Http\Request;

class UnlockUserVault
{
    public function __construct(
        private FinancialEncryptionService $encryption,
        private VaultKeyManager $vaultKeyManager,
    ) {}

    public function handle(Request $request, callable $next): mixed
    {
        $password = $request->input('password');

        if (is_string($password) && $password !== '') {
            $request->session()->put('vault.pending_password', encrypt($password));
        }

        $response = $next($request);

        $user = $request->user();

        if ($user instanceof User) {
            $this->unlockForUser($request, $user);
        }

        return $response;
    }

    public function unlockForUser(Request $request, User $user): void
    {
        if ($user->vault === null || $user->vault->is_locked) {
            return;
        }

        $password = $request->input('password');

        if (! is_string($password) || $password === '') {
            $pending = $request->session()->pull('vault.pending_password');

            if (is_string($pending)) {
                $password = decrypt($pending);
            }
        } else {
            $request->session()->forget('vault.pending_password');
        }

        if (! is_string($password) || $password === '') {
            return;
        }

        try {
            $dek = $this->encryption->unlockWithPassword($user->vault, $password);
            $this->vaultKeyManager->storeUserDek($dek);
        } catch (\Throwable) {
            //
        }
    }
}
