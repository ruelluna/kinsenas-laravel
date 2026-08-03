<?php

namespace App\Notifications\Savings;

use App\Enums\NotificationKind;
use App\Models\FundTransfer;
use App\Models\Team;
use App\Notifications\Concerns\FormatsDatabaseNotification;
use App\Services\Notifications\NotificationPreferenceService;
use App\Support\Notifications\NotificationActionUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class PendingTransferConfirmation extends Notification implements ShouldQueue
{
    use FormatsDatabaseNotification, Queueable;

    public function __construct(public FundTransfer $transfer) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)
            ->channelsFor($notifiable, NotificationKind::PendingTransferConfirmation);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $team = $this->team();

        return $this->databasePayload(
            NotificationKind::PendingTransferConfirmation,
            __('Pending transfer needs confirmation'),
            __('A cross-bank transfer is waiting for confirmation.'),
            $team !== null ? "/{$team->slug}/savings/transfers" : NotificationActionUrl::LAUNCH,
            [
                'transferId' => $this->transfer->id,
                'teamId' => $team?->id,
                'teamSlug' => $team?->slug,
            ],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $team = $this->team();

        return (new MailMessage)
            ->subject(__('Pending transfer needs confirmation'))
            ->line(__('A cross-bank transfer is waiting for confirmation in :team.', [
                'team' => $team?->name ?? config('app.name'),
            ]))
            ->action(
                __('Review transfers'),
                url($team !== null ? "/{$team->slug}/savings/transfers" : NotificationActionUrl::LAUNCH),
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
        $this->transfer->loadMissing('plan.team');

        return $this->transfer->plan?->team;
    }
}
