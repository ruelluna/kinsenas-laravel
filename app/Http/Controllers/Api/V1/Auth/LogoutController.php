<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogoutController extends Controller
{
    public function __invoke(Request $request, VaultKeyManager $vaultKeyManager): Response
    {
        $vaultKeyManager->forgetAll();

        $request->user()->currentAccessToken()?->delete();

        return response()->noContent();
    }
}
