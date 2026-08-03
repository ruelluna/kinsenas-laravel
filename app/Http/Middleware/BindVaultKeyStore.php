<?php

namespace App\Http\Middleware;

use App\Contracts\Vault\VaultKeyStore;
use App\Services\Vault\SessionVaultKeyStore;
use App\Services\Vault\TokenVaultKeyStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BindVaultKeyStore
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if ($token !== null) {
            app()->instance(VaultKeyStore::class, new TokenVaultKeyStore($token->id));
        } else {
            app()->instance(VaultKeyStore::class, app(SessionVaultKeyStore::class));
        }

        return $next($request);
    }
}
