<?php

namespace App\Notifications\Savings;

use App\Enums\NotificationKind;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Notifications\Concerns\FormatsDatabaseNotification;
use App\Services\Notifications\NotificationPreferenceService;
use App\Support\Notifications\NotificationActionUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class IncomeReminder extends Notification implements ShouldQueue
{
    use FormatsDatabaseNotification, Queueable;

    public function __construct(public SavingsPlan $plan) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)
            ->channelsFor($notifiable, NotificationKind::IncomeReminder);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $team = $this->team();

        return $this->databasePayload(
            NotificationKind::IncomeReminder,
            __('Log your income'),
            __('It looks like you have not logged income for this month yet.'),
            $team !== null ? "/{$team->slug}/savings/income" : NotificationActionUrl::LAUNCH,
            [
                'planId' => $this->plan->id,
                'teamId' => $team?->id,
                'teamSlug' => $team?->slug,
            ],
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

    private function team(): ?Team
    {
        $this->plan->loadMissing('team');

        return $this->plan->team;
    }
}
