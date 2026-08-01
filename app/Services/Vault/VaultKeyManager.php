<?php

namespace App\Services\Vault;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class VaultKeyManager
{
    public const string SESSION_USER_DEK = 'vault.user_dek';

    public const string SESSION_TEAM_DEK_PREFIX = 'vault.team_dek.';

    public function __construct(private FinancialEncryptionService $encryption) {}

    public function storeUserDek(string $dek): void
    {
        Session::put(self::SESSION_USER_DEK, base64_encode($dek));
    }

    public function storeTeamDek(Team $team, string $dek): void
    {
        Session::put(self::SESSION_TEAM_DEK_PREFIX.$team->id, base64_encode($dek));
    }

    public function forgetAll(): void
    {
        Session::forget(self::SESSION_USER_DEK);

        foreach (array_keys(Session::all()) as $key) {
            if (str_starts_with($key, self::SESSION_TEAM_DEK_PREFIX)) {
                Session::forget($key);
            }
        }
    }

    public function hasUserDek(): bool
    {
        return Session::has(self::SESSION_USER_DEK);
    }

    public function userDek(): ?string
    {
        $encoded = Session::get(self::SESSION_USER_DEK);

        if (! is_string($encoded)) {
            return null;
        }

        $decoded = base64_decode($encoded, true);

        return $decoded === false ? null : $decoded;
    }

    public function teamDek(Team $team): ?string
    {
        $encoded = Session::get(self::SESSION_TEAM_DEK_PREFIX.$team->id);

        if (! is_string($encoded)) {
            return null;
        }

        $decoded = base64_decode($encoded, true);

        return $decoded === false ? null : $decoded;
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
