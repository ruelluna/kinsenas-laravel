<?php

namespace App\Contracts\Vault;

interface VaultKeyStore
{
    public function storeUserDek(string $dek): void;

    public function storeTeamDek(string $teamId, string $dek): void;

    public function forgetAll(): void;

    public function hasUserDek(): bool;

    public function userDek(): ?string;

    public function teamDek(string $teamId): ?string;
}
