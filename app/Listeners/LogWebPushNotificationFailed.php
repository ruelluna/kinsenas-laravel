<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\Events\NotificationFailed;

class LogWebPushNotificationFailed
{
    public function handle(NotificationFailed $event): void
    {
        $subscription = $event->subscription;
        $report = $event->report;

        Log::warning('Web Push notification failed', [
            'endpoint' => $subscription->endpoint,
            'status_code' => $report->getStatusCode(),
            'reason' => $report->getReason(),
            'expired' => $report->isSubscriptionExpired(),
            'subscribable_type' => $subscription->subscribable_type,
            'subscribable_id' => $subscription->subscribable_id,
            'content_encoding' => $subscription->content_encoding?->value ?? $subscription->content_encoding,
        ]);
    }
}
