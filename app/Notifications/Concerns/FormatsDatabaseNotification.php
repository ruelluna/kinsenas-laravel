<?php

namespace App\Notifications\Concerns;

use App\Enums\NotificationKind;

trait FormatsDatabaseNotification
{
    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected function databasePayload(
        NotificationKind $kind,
        string $title,
        string $body,
        string $actionUrl,
        array $meta = [],
    ): array {
        return [
            'kind' => $kind->value,
            'title' => $title,
            'body' => $body,
            'actionUrl' => $actionUrl,
            'meta' => $meta,
        ];
    }
}
