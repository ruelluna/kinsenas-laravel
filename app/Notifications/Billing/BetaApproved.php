<?php

namespace App\Notifications\Billing;

use App\Enums\NotificationKind;
use App\Notifications\Concerns\FormatsDatabaseNotification;
use App\Services\Notifications\NotificationPreferenceService;
use App\Support\Notifications\NotificationActionUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class BetaApproved extends Notification implements ShouldQueue
{
    use FormatsDatabaseNotification, Queueable;

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)
            ->channelsFor($notifiable, NotificationKind::BetaApproved);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->databasePayload(
            NotificationKind::BetaApproved,
            __('Beta access approved'),
            __('Your Kinsenas beta application was approved. You now have full access.'),
            NotificationActionUrl::LAUNCH,
            [],
        );
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        $payload = $this->toArray($notifiable);

        return (new WebPushMessage)
            ->title($payload['title'])
            ->body($payload['body'])
            ->data(['actionUrl' => $payload['actionUrl']])
            ->options(['TTL' => 86400]);
    }
}
