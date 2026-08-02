<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Marketing\GhlMarketingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncUserTagsToGhl implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<string>  $tagsToAdd
     * @param  list<string>  $tagsToRemove
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public User $user,
        public array $tagsToAdd = [],
        public array $tagsToRemove = [],
        public array $context = [],
    ) {}

    public function handle(GhlMarketingService $ghlMarketingService): void
    {
        $ghlMarketingService->syncUserTags(
            $this->user,
            $this->tagsToAdd,
            $this->tagsToRemove,
            $this->context,
        );
    }
}
