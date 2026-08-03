<?php

namespace App\Notifications\Savings;

use App\Enums\NotificationKind;
use App\Models\SavingsCategory;
use App\Models\Team;
use App\Notifications\Concerns\FormatsDatabaseNotification;
use App\Services\Notifications\NotificationPreferenceService;
use App\Support\Notifications\NotificationActionUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class LowFundBalance extends Notification implements ShouldQueue
{
    use FormatsDatabaseNotification, Queueable;

    public function __construct(
        public SavingsCategory $category,
        public int $percentUsed,
    ) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)
            ->channelsFor($notifiable, NotificationKind::LowFundBalance);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $team = $this->team();

        return $this->databasePayload(
            NotificationKind::LowFundBalance,
            __('Fund bucket running low'),
            __(':fund is :percent% used this period.', [
                'fund' => $this->category->name,
                'percent' => $this->percentUsed,
            ]),
            NotificationActionUrl::teamDashboard($team),
            [
                'categoryId' => $this->category->id,
                'teamId' => $team?->id,
                'teamSlug' => $team?->slug,
                'percentUsed' => $this->percentUsed,
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
        $this->category->loadMissing('plan.team');

        return $this->category->plan?->team;
    }
}
