<?php

namespace App\Http\Middleware;

use App\Services\Vault\VaultKeyManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVaultUnlocked
{
    public function __construct(private VaultKeyManager $vaultKeyManager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if ($user->vault === null || $this->vaultKeyManager->hasUserDek()) {
            return $next($request);
        }

        if ($request->routeIs('vault.*', 'settings.security', 'logout')) {
            return $next($request);
        }

        return redirect()->route('vault.unlock');
    }
}
