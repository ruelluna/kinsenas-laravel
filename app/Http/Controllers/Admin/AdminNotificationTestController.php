<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendTestPushRequest;
use App\Models\User;
use App\Notifications\System\TestPushNotification;
use App\Services\Notifications\PushNotificationDiagnostics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminNotificationTestController extends Controller
{
    public function __construct(private PushNotificationDiagnostics $diagnostics) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('admin/notifications-test/index', [
            'subscriberCount' => User::query()
                ->whereHas('pushSubscriptions')
                ->whereHas('notificationPreferences', fn ($query) => $query->where('push_enabled', true))
                ->count(),
            'serverStatus' => $this->diagnostics->serverStatus(),
            'userStatus' => $user !== null ? $this->diagnostics->forUser($user) : null,
            'checklist' => $user !== null ? $this->diagnostics->checklistForUser($user) : [],
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
            return to_route('admin.notifications-test.index')
                ->withErrors(['targetEmail' => __('No user found with that email.')]);
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

        $message = __('Sent :count test push notification(s).', ['count' => $sent]);
        $hint = __('Check the bell inbox on the target account. An OS notification confirms the service worker received the push.');

        return to_route('admin.notifications-test.index')
            ->with('success', "{$message} {$hint}");
    }
}
