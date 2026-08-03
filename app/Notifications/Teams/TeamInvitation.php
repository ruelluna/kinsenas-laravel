<?php

namespace App\Notifications\Teams;

use App\Enums\NotificationKind;
use App\Models\TeamInvitation as TeamInvitationModel;
use App\Notifications\Concerns\FormatsDatabaseNotification;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class TeamInvitation extends Notification implements ShouldQueue
{
    use FormatsDatabaseNotification, Queueable;

    public function __construct(public TeamInvitationModel $invitation) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }

        return app(NotificationPreferenceService::class)
            ->channelsFor($notifiable, NotificationKind::TeamInvitation);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $team = $this->invitation->team;
        $inviter = $this->invitation->inviter;

        return (new MailMessage)
            ->subject(__("You've been invited to join :teamName", ['teamName' => $team->name]))
            ->line(__(':inviterName has invited you to join the :teamName team.', [
                'inviterName' => $inviter->name,
                'teamName' => $team->name,
            ]))
            ->line(__('Log in and visit your dashboard to accept or decline this invitation.'))
            ->action(
                __('Log in'),
                route('login', ['invitation' => $this->invitation->code]),
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $team = $this->invitation->team;

        return $this->databasePayload(
            NotificationKind::TeamInvitation,
            __('Team invitation'),
            __(':inviter invited you to join :team.', [
                'inviter' => $this->invitation->inviter->name,
                'team' => $team->name,
            ]),
            '/dashboard',
            [
                'invitationId' => $this->invitation->id,
                'teamId' => $team->id,
                'teamSlug' => $team->slug,
                'teamName' => $team->name,
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
}
