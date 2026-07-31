<?php

namespace Tests\Concerns;

use App\Models\User;
use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;

trait UnlocksVault
{
    protected function unlockVaultFor(User $user, string $password = 'password'): void
    {
        $vault = $user->vault;

        if ($vault === null) {
            app(FinancialEncryptionService::class)->createUserVault($user, $password);
            $vault = $user->fresh()->vault;
        }

        $dek = app(FinancialEncryptionService::class)->unlockWithPassword($vault, $password);
        app(VaultKeyManager::class)->storeUserDek($dek);
    }
}
