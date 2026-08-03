<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminSubscriberResource;
use App\Models\Team;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $teams = Team::query()
            ->with(['subscription.plan'])
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($status === 'none', fn ($query) => $query->whereDoesntHave('subscription'))
            ->when($status && $status !== 'none', function ($query) use ($status) {
                $query->whereHas('subscription', fn ($q) => $q->where('status', $status));
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return AdminSubscriberResource::collection($teams);
    }

    public function show(Team $team)
    {
        $team->load(['subscription.plan']);

        return new AdminSubscriberResource($team);
    }
}
