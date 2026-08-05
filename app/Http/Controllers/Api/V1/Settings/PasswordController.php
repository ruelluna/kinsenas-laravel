<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Http\JsonResponse;

class PasswordController extends Controller
{
    public function __construct(
        private FinancialEncryptionService $encryption,
        private VaultKeyManager $vaultKeyManager,
    ) {}

    public function update(PasswordUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->vault !== null) {
            $this->encryption->rewrapWithPassword(
                $user->vault,
                $request->input('current_password'),
                $request->password,
            );

            $dek = $this->encryption->unlockWithPassword($user->vault->fresh(), $request->password);
            $this->vaultKeyManager->storeUserDek($dek);
        }

        $user->update(['password' => $request->password]);

        return response()->json([
            'message' => __('Password updated.'),
        ]);
    }
}
