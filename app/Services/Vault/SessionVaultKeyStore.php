<?php

namespace App\Services\Vault;

use App\Contracts\Vault\VaultKeyStore;
use Illuminate\Support\Facades\Session;

class SessionVaultKeyStore implements VaultKeyStore
{
    public const string SESSION_USER_DEK = 'vault.user_dek';

    public const string SESSION_TEAM_DEK_PREFIX = 'vault.team_dek.';

    public function storeUserDek(string $dek): void
    {
        Session::put(self::SESSION_USER_DEK, base64_encode($dek));
    }

    public function storeTeamDek(string $teamId, string $dek): void
    {
        Session::put(self::SESSION_TEAM_DEK_PREFIX.$teamId, base64_encode($dek));
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
        return $this->decode(Session::get(self::SESSION_USER_DEK));
    }

    public function teamDek(string $teamId): ?string
    {
        return $this->decode(Session::get(self::SESSION_TEAM_DEK_PREFIX.$teamId));
    }

    private function decode(mixed $encoded): ?string
    {
        if (! is_string($encoded)) {
            return null;
        }

        $decoded = base64_decode($encoded, true);

        return $decoded === false ? null : $decoded;
    }
}
