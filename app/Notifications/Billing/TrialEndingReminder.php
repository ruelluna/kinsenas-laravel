<?php

namespace App\Notifications\Billing;

use App\Enums\NotificationKind;
use App\Models\Subscription;
use App\Models\Team;
use App\Notifications\Concerns\FormatsDatabaseNotification;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class TrialEndingReminder extends Notification implements ShouldQueue
{
    use FormatsDatabaseNotification, Queueable;

    public function __construct(public Subscription $subscription, public int $daysRemaining) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)
            ->channelsFor($notifiable, NotificationKind::TrialEndingReminder);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $team = $this->team();

        return $this->databasePayload(
            NotificationKind::TrialEndingReminder,
            __('Trial ending soon'),
            __('Your Kinsenas trial ends in :days day(s).', ['days' => $this->daysRemaining]),
            '/settings/billing',
            [
                'subscriptionId' => $this->subscription->id,
                'teamId' => $team?->id,
                'teamSlug' => $team?->slug,
                'daysRemaining' => $this->daysRemaining,
            ],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $team = $this->team();

        return (new MailMessage)
            ->subject(__('Your Kinsenas trial is ending soon'))
            ->line(__('Your trial for :team ends in :days day(s).', [
                'team' => $team?->name ?? config('app.name'),
                'days' => $this->daysRemaining,
            ]))
            ->action(__('Manage billing'), url('/settings/billing'));
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

    private function team(): ?Team
    {
        $this->subscription->loadMissing('team');

        return $this->subscription->team;
    }
}
