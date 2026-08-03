<?php

namespace App\Support\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;

class NotificationPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function fromDatabaseNotification(DatabaseNotification $notification): array
    {
        /** @var array<string, mixed> $data */
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'kind' => $data['kind'] ?? 'unknown',
            'title' => $data['title'] ?? '',
            'body' => $data['body'] ?? '',
            'actionUrl' => $data['actionUrl'] ?? null,
            'meta' => $data['meta'] ?? [],
            'readAt' => $notification->read_at?->toISOString(),
            'createdAt' => $notification->created_at?->toISOString(),
            'isUnread' => $notification->read_at === null,
        ];
    }

    /**
     * @param  iterable<DatabaseNotification>  $notifications
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $notifications): array
    {
        $items = [];

        foreach ($notifications as $notification) {
            $items[] = self::fromDatabaseNotification($notification);
        }

        return $items;
    }

    public static function relativeTime(?Carbon $createdAt): string
    {
        return $createdAt?->diffForHumans() ?? '';
    }
}
