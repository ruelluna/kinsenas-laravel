<?php

namespace App\Services\Vault;

use App\Models\User;
use App\Models\UserVault;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class FinancialEncryptionService
{
    private const int PBKDF2_ITERATIONS = 100_000;

    public function generateDek(): string
    {
        return random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    }

    public function generateRecoveryKey(): string
    {
        return Str::upper(Str::replace('-', '', (string) Str::uuid())).'-'.Str::upper(Str::replace('-', '', (string) Str::uuid()));
    }

    public function deriveKey(string $secret, string $salt): string
    {
        return hash_pbkdf2('sha256', $secret, $salt, self::PBKDF2_ITERATIONS, SODIUM_CRYPTO_SECRETBOX_KEYBYTES, true);
    }

    public function wrapDek(string $dek, string $secret, string $salt): string
    {
        $key = $this->deriveKey($secret, $salt);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($dek, $nonce, $key);

        return base64_encode($nonce.$cipher);
    }

    public function unwrapDek(string $wrappedDek, string $secret, string $salt): string
    {
        $decoded = base64_decode($wrappedDek, true);

        if ($decoded === false || strlen($decoded) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES) {
            throw new RuntimeException('Invalid wrapped key payload.');
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $key = $this->deriveKey($secret, $salt);

        $dek = sodium_crypto_secretbox_open($cipher, $nonce, $key);

        if ($dek === false) {
            throw new RuntimeException('Unable to unwrap vault key.');
        }

        return $dek;
    }

    public function encryptAmount(string $dek, string $amount): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($amount, $nonce, $dek);

        return base64_encode($nonce.$cipher);
    }

    public function decryptAmount(string $dek, string $ciphertext): string
    {
        $decoded = base64_decode($ciphertext, true);

        if ($decoded === false || strlen($decoded) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES) {
            throw new RuntimeException('Invalid encrypted amount payload.');
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $plain = sodium_crypto_secretbox_open($cipher, $nonce, $dek);

        if ($plain === false) {
            throw new RuntimeException('Unable to decrypt amount.');
        }

        return $plain;
    }

    public function createUserVault(User $user, string $password, ?string $recoveryKey = null): array
    {
        $dek = $this->generateDek();
        $salt = bin2hex(random_bytes(16));
        $recoveryKey ??= $this->generateRecoveryKey();

        $vault = UserVault::query()->create([
            'user_id' => $user->id,
            'wrapped_dek' => $this->wrapDek($dek, $password, $salt),
            'recovery_wrapped_dek' => $this->wrapDek($dek, $recoveryKey, $salt),
            'salt' => $salt,
            'recovery_key_hash' => Hash::make($recoveryKey),
            'is_locked' => false,
        ]);

        return [
            'vault' => $vault,
            'recovery_key' => $recoveryKey,
        ];
    }

    public function rewrapWithPassword(UserVault $vault, string $currentPassword, string $newPassword): void
    {
        $dek = $this->unwrapDek($vault->wrapped_dek, $currentPassword, $vault->salt);

        $vault->update([
            'wrapped_dek' => $this->wrapDek($dek, $newPassword, $vault->salt),
        ]);
    }

    public function unlockWithPassword(UserVault $vault, string $password): string
    {
        if ($vault->is_locked) {
            throw new RuntimeException('Vault is locked. Use your recovery key.');
        }

        return $this->unwrapDek($vault->wrapped_dek, $password, $vault->salt);
    }

    public function unlockWithRecoveryKey(UserVault $vault, string $recoveryKey): string
    {
        if ($vault->recovery_wrapped_dek === null || $vault->recovery_key_hash === null) {
            throw new RuntimeException('No recovery key configured.');
        }

        if (! Hash::check($recoveryKey, $vault->recovery_key_hash)) {
            throw new RuntimeException('Invalid recovery key.');
        }

        $dek = $this->unwrapDek($vault->recovery_wrapped_dek, $recoveryKey, $vault->salt);

        $vault->update(['is_locked' => false]);

        return $dek;
    }

    public function lockVault(UserVault $vault): void
    {
        $vault->update(['is_locked' => true]);
    }

    public function tryDecryptForDisplay(string $dek, ?string $ciphertext): ?string
    {
        if ($ciphertext === null || $ciphertext === '') {
            return null;
        }

        try {
            return $this->decryptAmount($dek, $ciphertext);
        } catch (RuntimeException|DecryptException) {
            return null;
        }
    }
}
