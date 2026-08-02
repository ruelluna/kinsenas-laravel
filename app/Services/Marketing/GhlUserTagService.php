<?php

namespace App\Services\Marketing;

use App\Jobs\SyncUserTagsToGhl;
use App\Models\User;

class GhlUserTagService
{
    /**
     * @param  list<string>  $tagsToAdd
     * @param  list<string>  $tagsToRemove
     * @param  array<string, mixed>  $context
     */
    public function dispatch(User $user, array $tagsToAdd = [], array $tagsToRemove = [], array $context = []): void
    {
        if ($tagsToAdd === [] && $tagsToRemove === []) {
            return;
        }

        SyncUserTagsToGhl::dispatch($user, $tagsToAdd, $tagsToRemove, $context)->afterCommit();
    }
}
