<?php

namespace App\Services\Vault;

use App\Contracts\Vault\VaultKeyStore;
use Illuminate\Support\Facades\Cache;

class TokenVaultKeyStore implements VaultKeyStore
{
    private const int TTL_SECONDS = 86400;

    public function __construct(private int $tokenId) {}

    public function storeUserDek(string $dek): void
    {
        Cache::put($this->userKey(), base64_encode($dek), self::TTL_SECONDS);
    }

    public function storeTeamDek(string $teamId, string $dek): void
    {
        Cache::put($this->teamKey($teamId), base64_encode($dek), self::TTL_SECONDS);
    }

    public function forgetAll(): void
    {
        Cache::forget($this->userKey());

        foreach (Cache::get($this->teamIndexKey(), []) as $teamId) {
            Cache::forget($this->teamKey((string) $teamId));
        }

        Cache::forget($this->teamIndexKey());
    }

    public function hasUserDek(): bool
    {
        return Cache::has($this->userKey());
    }

    public function userDek(): ?string
    {
        return $this->decode(Cache::get($this->userKey()));
    }

    public function teamDek(string $teamId): ?string
    {
        return $this->decode(Cache::get($this->teamKey($teamId)));
    }

    private function userKey(): string
    {
        return "vault:token:{$this->tokenId}:user_dek";
    }

    private function teamKey(string $teamId): string
    {
        $index = Cache::get($this->teamIndexKey(), []);

        if (! in_array($teamId, $index, true)) {
            $index[] = $teamId;
            Cache::put($this->teamIndexKey(), $index, self::TTL_SECONDS);
        }

        return "vault:token:{$this->tokenId}:team_dek:{$teamId}";
    }

    private function teamIndexKey(): string
    {
        return "vault:token:{$this->tokenId}:team_ids";
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
