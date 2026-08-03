<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Api\SharedPropsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request, SharedPropsService $sharedProps): JsonResponse
    {
        return response()->json($sharedProps->forUser($request->user()));
    }
}
