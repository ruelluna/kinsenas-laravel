<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vault\UnlockVaultRequest;
use App\Services\Marketing\ActivationGhlTagService;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Http\JsonResponse;

class VaultUnlockController extends Controller
{
    public function __invoke(
        UnlockVaultRequest $request,
        VaultKeyManager $vaultKeyManager,
        ActivationGhlTagService $activationGhlTagService,
    ): JsonResponse {
        $user = $request->user();

        if ($request->filled('recovery_key')) {
            $vaultKeyManager->unlockWithRecoveryKey($user, $request->string('recovery_key')->value());
        } else {
            $vaultKeyManager->unlockForUser($user, $request->string('password')->value());
        }

        $activationGhlTagService->syncVaultUnlocked($user);

        return response()->json([
            'message' => __('Vault unlocked.'),
            'vaultLocked' => false,
        ]);
    }
}
