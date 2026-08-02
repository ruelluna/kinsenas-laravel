<?php

namespace App\Http\Controllers\Vault;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vault\UnlockVaultRequest;
use App\Services\Marketing\ActivationGhlTagService;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VaultUnlockController extends Controller
{
    public function __construct(
        private VaultKeyManager $vaultKeyManager,
        private ActivationGhlTagService $activationGhlTagService,
    ) {}

    public function create(): Response
    {
        return Inertia::render('vault/unlock', [
            'isLocked' => auth()->user()?->vault?->is_locked ?? false,
        ]);
    }

    public function store(UnlockVaultRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($request->filled('recovery_key')) {
            $this->vaultKeyManager->unlockWithRecoveryKey($user, $request->string('recovery_key')->value());
        } else {
            $this->vaultKeyManager->unlockForUser($user, $request->string('password')->value());
        }

        $this->activationGhlTagService->syncVaultUnlocked($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Vault unlocked.')]);

        return redirect()->intended(route('dashboard', ['current_team' => $user->currentTeam?->slug]));
    }
}
