<?php

namespace App\Services\Vault;

use App\Contracts\Vault\VaultKeyStore;
use App\Models\Team;
use App\Models\User;

class VaultKeyManager
{
    public const string SESSION_USER_DEK = SessionVaultKeyStore::SESSION_USER_DEK;

    public function __construct(
        private FinancialEncryptionService $encryption,
        private VaultKeyStore $store,
    ) {}

    public function storeUserDek(string $dek): void
    {
        $this->store->storeUserDek($dek);
    }

    public function storeTeamDek(Team $team, string $dek): void
    {
        $this->store->storeTeamDek((string) $team->id, $dek);
    }

    public function forgetAll(): void
    {
        $this->store->forgetAll();
    }

    public function hasUserDek(): bool
    {
        return $this->store->hasUserDek();
    }

    public function userDek(): ?string
    {
        return $this->store->userDek();
    }

    public function teamDek(Team $team): ?string
    {
        return $this->store->teamDek((string) $team->id);
    }

    public function unlockForUser(User $user, string $password): void
    {
        $vault = $user->vault;

        if ($vault === null) {
            return;
        }

        $dek = $this->encryption->unlockWithPassword($vault, $password);
        $this->storeUserDek($dek);
    }

    public function unlockWithRecoveryKey(User $user, string $recoveryKey): void
    {
        $vault = $user->vault;

        if ($vault === null) {
            throw new \RuntimeException('Vault not found.');
        }

        $dek = $this->encryption->unlockWithRecoveryKey($vault, $recoveryKey);
        $this->storeUserDek($dek);
    }

    public function activeDekForTeam(Team $team, User $user, bool $planIsShared): ?string
    {
        if ($planIsShared) {
            $teamDek = $this->teamDek($team);

            if ($teamDek !== null) {
                return $teamDek;
            }

            $teamVault = $team->vault;

            if ($teamVault !== null && $this->hasUserDek()) {
                $userDek = $this->userDek();
                $this->storeTeamDek($team, $userDek);

                return $userDek;
            }
        }

        return $this->userDek();
    }

    public function requireUserDek(): string
    {
        $dek = $this->userDek();

        if ($dek === null) {
            throw new \RuntimeException('Vault is locked.');
        }

        return $dek;
    }

    public function requireDekForTeam(Team $team, bool $planIsShared): string
    {
        $dek = $this->activeDekForTeam($team, auth()->user(), $planIsShared);

        if ($dek === null) {
            throw new \RuntimeException('Vault is locked.');
        }

        return $dek;
    }
}
