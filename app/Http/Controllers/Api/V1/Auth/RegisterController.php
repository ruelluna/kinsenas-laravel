<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function __invoke(Request $request, CreateNewUser $createNewUser): JsonResponse
    {
        $credentials = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'password_confirmation' => ['required', 'string'],
            'marketing_emails_opt_in' => ['sometimes', 'boolean'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $result = $createNewUser->register($credentials);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        $user = $result['user'];
        $token = $user->createToken($credentials['device_name'] ?? 'mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'recovery_key' => $result['recovery_key'],
            'user' => [
                ...$user->toArray(),
                'isPlatformAdmin' => $user->isPlatformAdmin(),
            ],
        ], 201);
    }
}
