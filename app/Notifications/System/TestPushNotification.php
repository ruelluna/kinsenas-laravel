<?php

namespace App\Notifications\System;

use App\Enums\NotificationKind;
use App\Notifications\Concerns\FormatsDatabaseNotification;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TestPushNotification extends Notification implements ShouldQueue
{
    use FormatsDatabaseNotification, Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public string $actionUrl = '/dashboard',
    ) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        $preferenceService = app(NotificationPreferenceService::class);
        $channels = ['database'];

        if ($preferenceService->wantsWebPush(
            $notifiable,
            $preferenceService->forUser($notifiable),
            NotificationKind::TestPush,
        )) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->databasePayload(
            NotificationKind::TestPush,
            $this->title,
            $this->body,
            $this->actionUrl,
            ['test' => true],
        );
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->data(['actionUrl' => $this->actionUrl])
            ->options(['TTL' => 3600]);
    }
}
