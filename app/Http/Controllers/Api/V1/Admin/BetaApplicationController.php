<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class BetaApplicationController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->whereNotNull('beta_application_status')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->beta_application_status?->value,
                'enrolledAt' => $user->beta_enrolled_at?->toISOString(),
                'approvedAt' => $user->beta_approved_at?->toISOString(),
            ]);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'currentPage' => $users->currentPage(),
                'lastPage' => $users->lastPage(),
                'perPage' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }
}
