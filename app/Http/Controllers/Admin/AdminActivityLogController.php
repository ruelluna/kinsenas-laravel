<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserActivityAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class AdminActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $event = $request->query('event');
        $teamId = $request->query('team_id');

        $activities = Activity::query()
            ->where('log_name', 'kinsenas')
            ->with(['causer', 'subject'])
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhereHas('causer', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($event, fn ($query) => $query->where('event', $event))
            ->when($teamId, fn ($query) => $query->where('properties->team_id', $teamId))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('admin/activity-logs/index', [
            'activities' => $activities->through(fn (Activity $activity) => [
                'id' => $activity->id,
                'description' => $activity->description,
                'event' => $activity->event,
                'eventLabel' => $activity->event !== null
                    ? (UserActivityAction::tryFrom($activity->event)?->label() ?? $activity->event)
                    : null,
                'causerName' => $activity->causer?->name,
                'causerEmail' => $activity->causer?->email,
                'teamName' => data_get($activity->properties, 'team_name'),
                'teamId' => data_get($activity->properties, 'team_id'),
                'createdAt' => $activity->created_at?->toISOString(),
            ]),
            'filters' => [
                'search' => $search,
                'event' => $event,
                'team_id' => $teamId,
            ],
            'events' => collect(UserActivityAction::cases())
                ->map(fn (UserActivityAction $action) => [
                    'value' => $action->value,
                    'label' => $action->label(),
                ])
                ->values()
                ->all(),
        ]);
    }
}
