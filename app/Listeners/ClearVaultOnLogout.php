<?php

namespace App\Listeners;

use App\Services\Vault\VaultKeyManager;
use Illuminate\Auth\Events\Logout;

class ClearVaultOnLogout
{
    public function __construct(private VaultKeyManager $vaultKeyManager)
    {
    }

    public function handle(Logout $event): void
    {
        $this->vaultKeyManager->forgetAll();
    }
}
