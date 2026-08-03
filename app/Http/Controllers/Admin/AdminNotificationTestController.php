<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendTestPushRequest;
use App\Models\User;
use App\Notifications\System\TestPushNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminNotificationTestController extends Controller
{
    public function index(Request $request): Response
    {
        $subscriberCount = User::query()
            ->whereHas('pushSubscriptions')
            ->whereHas('notificationPreferences', fn ($query) => $query->where('push_enabled', true))
            ->count();

        return Inertia::render('admin/notifications-test/index', [
            'subscriberCount' => $subscriberCount,
            'defaults' => [
                'title' => 'Kinsenas test push',
                'body' => 'If you see this, Web Push is working.',
                'actionUrl' => '/dashboard',
            ],
        ]);
    }

    public function store(SendTestPushRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();

        $notification = new TestPushNotification(
            $validated['title'],
            $validated['body'],
            $validated['actionUrl'],
        );

        $target = match ($validated['target']) {
            'self' => $actor,
            'all' => null,
            default => User::query()->where('email', $validated['targetEmail'] ?? '')->first(),
        };

        if ($validated['target'] === 'email' && $target === null) {
            return back()->withErrors(['targetEmail' => __('No user found with that email.')]);
        }

        $sent = 0;

        if ($validated['target'] === 'all') {
            User::query()
                ->whereHas('pushSubscriptions')
                ->whereHas('notificationPreferences', fn ($query) => $query->where('push_enabled', true))
                ->each(function (User $user) use ($notification, &$sent): void {
                    $user->notify($notification);
                    $sent++;
                });
        } else {
            $target?->notify($notification);
            $sent = 1;
        }

        return back()->with('success', __('Sent :count test push notification(s).', ['count' => $sent]));
    }
}
