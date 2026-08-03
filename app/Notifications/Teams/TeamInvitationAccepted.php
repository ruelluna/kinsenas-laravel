<?php

namespace App\Notifications\Teams;

use App\Enums\NotificationKind;
use App\Models\Team;
use App\Models\TeamInvitation as TeamInvitationModel;
use App\Models\User;
use App\Notifications\Concerns\FormatsDatabaseNotification;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class TeamInvitationAccepted extends Notification implements ShouldQueue
{
    use FormatsDatabaseNotification, Queueable;

    public function __construct(
        public TeamInvitationModel $invitation,
        public User $acceptedBy,
    ) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)
            ->channelsFor($notifiable, NotificationKind::TeamInvitationAccepted);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $team = $this->team();

        return $this->databasePayload(
            NotificationKind::TeamInvitationAccepted,
            __('Invitation accepted'),
            __(':name joined :team.', [
                'name' => $this->acceptedBy->name,
                'team' => $team->name,
            ]),
            '/settings/teams/'.$team->slug,
            [
                'invitationId' => $this->invitation->id,
                'teamId' => $team->id,
                'teamSlug' => $team->slug,
                'acceptedByUserId' => $this->acceptedBy->id,
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

    private function team(): Team
    {
        $this->invitation->loadMissing('team');

        return $this->invitation->team;
    }
}
