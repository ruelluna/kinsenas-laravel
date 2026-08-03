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

class SubscriptionPastDue extends Notification implements ShouldQueue
{
    use FormatsDatabaseNotification, Queueable;

    public function __construct(public Subscription $subscription) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)
            ->channelsFor($notifiable, NotificationKind::SubscriptionPastDue);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $team = $this->team();

        return $this->databasePayload(
            NotificationKind::SubscriptionPastDue,
            __('Subscription past due'),
            __('Your Kinsenas subscription for :team needs attention.', [
                'team' => $team?->name ?? config('app.name'),
            ]),
            '/settings/billing',
            [
                'subscriptionId' => $this->subscription->id,
                'teamId' => $team?->id,
                'teamSlug' => $team?->slug,
            ],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $team = $this->team();

        return (new MailMessage)
            ->subject(__('Your Kinsenas subscription is past due'))
            ->line(__('Your subscription for :team is past due. Update billing to restore full access.', [
                'team' => $team?->name ?? config('app.name'),
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
