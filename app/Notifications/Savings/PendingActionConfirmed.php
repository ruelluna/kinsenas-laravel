<?php

namespace App\Notifications\Savings;

use App\Enums\NotificationKind;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\Team;
use App\Notifications\Concerns\FormatsDatabaseNotification;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class PendingActionConfirmed extends Notification implements ShouldQueue
{
    use FormatsDatabaseNotification, Queueable;

    public function __construct(
        public FundSpend|FundTransfer $action,
        public string $actionType,
    ) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)
            ->channelsFor($notifiable, NotificationKind::PendingActionConfirmed);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $team = $this->team();
        $isSpend = $this->action instanceof FundSpend;

        return $this->databasePayload(
            NotificationKind::PendingActionConfirmed,
            $isSpend ? __('Spending confirmed') : __('Transfer confirmed'),
            $isSpend
                ? __('Your pending spend was confirmed.')
                : __('Your pending transfer was confirmed.'),
            $team !== null
                ? ($isSpend ? "/{$team->slug}/savings/spending" : "/{$team->slug}/savings/transfers")
                : '/dashboard',
            [
                'actionType' => $this->actionType,
                'spendId' => $isSpend ? $this->action->id : null,
                'transferId' => $isSpend ? null : $this->action->id,
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
        $this->action->loadMissing('plan.team');

        return $this->action->plan?->team;
    }
}
