<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\DeletePushSubscriptionRequest;
use App\Http\Requests\Settings\StorePushSubscriptionRequest;
use App\Http\Requests\Settings\UpdateNotificationPreferencesRequest;
use App\Notifications\System\TestPushNotification;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPreferenceController extends Controller
{
    public function __construct(private NotificationPreferenceService $preferenceService) {}

    public function edit(Request $request): Response
    {
        $user = $request->user();
        $preferences = $this->preferenceService->forUser($user);

        return Inertia::render('settings/notifications', [
            'preferences' => $this->preferenceService->toSharedArray($preferences),
            'paydayDayOfMonth' => $user->payday_day_of_month,
            'pushSubscriptionCount' => $user->pushSubscriptions()->count(),
            'vapidPublicKey' => config('webpush.vapid.public_key'),
        ]);
    }

    public function update(UpdateNotificationPreferencesRequest $request): RedirectResponse
    {
        $user = $request->user();
        $preferences = $this->preferenceService->forUser($user);
        $preferences->update($request->preferenceAttributes());

        $user->update([
            'payday_day_of_month' => $request->filled('paydayDayOfMonth')
                ? (int) $request->input('paydayDayOfMonth')
                : null,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Notification preferences updated.')]);

        return to_route('settings.notifications.edit');
    }

    public function storePushSubscription(StorePushSubscriptionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth'],
            $validated['contentEncoding'] ?? 'aes128gcm',
        );

        $preferences = $this->preferenceService->forUser($user);
        $preferences->update(['push_enabled' => true]);

        return response()->json([
            'pushEnabled' => true,
            'pushSubscriptionCount' => $user->pushSubscriptions()->count(),
        ]);
    }

    public function destroyPushSubscription(DeletePushSubscriptionRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->deletePushSubscription($request->validated('endpoint'));

        if ($user->pushSubscriptions()->doesntExist()) {
            $this->preferenceService->forUser($user)->update(['push_enabled' => false]);
        }

        return response()->json([
            'pushEnabled' => $user->pushSubscriptions()->exists(),
            'pushSubscriptionCount' => $user->pushSubscriptions()->count(),
        ]);
    }

    public function sendTestPush(Request $request): RedirectResponse
    {
        $user = $request->user();
        $preferences = $this->preferenceService->forUser($user);

        if (! $preferences->push_enabled || $user->pushSubscriptions()->doesntExist()) {
            return to_route('settings.notifications.edit')
                ->withErrors(['push' => __('Enable browser push on this device before sending a test notification.')]);
        }

        $user->notify(new TestPushNotification(
            __('Kinsenas test notification'),
            __('If you see this outside the app, push is working on this device.'),
        ));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Test notification queued. Leave the app or lock your phone to check the notification shade.'),
        ]);

        return to_route('settings.notifications.edit');
    }
}
