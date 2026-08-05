<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateNotificationPreferencesRequest;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function __construct(private NotificationPreferenceService $preferenceService) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'preferences' => $this->preferenceService->toSharedArray($this->preferenceService->forUser($user)),
            'paydayDayOfMonth' => $user->payday_day_of_month,
            'pushSubscriptionCount' => $user->pushSubscriptions()->count(),
            'vapidPublicKey' => config('webpush.vapid.public_key'),
        ]);
    }

    public function update(UpdateNotificationPreferencesRequest $request): JsonResponse
    {
        $user = $request->user();
        $preferences = $this->preferenceService->forUser($user);
        $preferences->update($request->preferenceAttributes());

        $user->update([
            'payday_day_of_month' => $request->filled('paydayDayOfMonth')
                ? (int) $request->input('paydayDayOfMonth')
                : null,
        ]);

        return response()->json([
            'message' => __('Notification preferences updated.'),
            'preferences' => $this->preferenceService->toSharedArray($preferences->fresh()),
            'paydayDayOfMonth' => $user->fresh()->payday_day_of_month,
        ]);
    }
}
