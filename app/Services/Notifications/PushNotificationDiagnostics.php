<?php

namespace App\Services\Notifications;

use App\Models\User;

class PushNotificationDiagnostics
{
    /**
     * @return array<string, mixed>
     */
    public function serverStatus(): array
    {
        return [
            'vapidConfigured' => filled(config('webpush.vapid.public_key'))
                && filled(config('webpush.vapid.private_key')),
            'vapidSubjectConfigured' => filled(config('webpush.vapid.subject')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $preferences = $user->notificationPreferences;

        return [
            'pushEnabled' => (bool) ($preferences?->push_enabled ?? false),
            'subscriptionCount' => $user->pushSubscriptions()->count(),
            'contentEncodings' => $user->pushSubscriptions()
                ->pluck('content_encoding')
                ->filter()
                ->values()
                ->all(),
        ];
    }

    /**
     * @return list<string>
     */
    public function checklistForUser(User $user): array
    {
        $items = [];
        $server = $this->serverStatus();
        $userStatus = $this->forUser($user);

        $items[] = $server['vapidConfigured']
            ? 'VAPID keys configured on server'
            : 'MISSING: VAPID public/private keys in server .env';

        $items[] = $userStatus['pushEnabled']
            ? 'push_enabled is true for user'
            : 'MISSING: user has not enabled browser push (Settings → Enable browser push)';

        $items[] = $userStatus['subscriptionCount'] > 0
            ? "User has {$userStatus['subscriptionCount']} push subscription(s)"
            : 'MISSING: no push_subscriptions row for this device';

        if ($userStatus['subscriptionCount'] > 0 && $userStatus['contentEncodings'] !== []) {
            $items[] = 'Content encoding(s): '.implode(', ', $userStatus['contentEncodings']);
        }

        $items[] = 'Inbox entry = notification job ran; OS popup = service worker + encoding + browser permission';

        return $items;
    }
}
