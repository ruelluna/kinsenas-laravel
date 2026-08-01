<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Marketing\GhlMarketingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncBetaApplicationToGhl implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $event,
    ) {}

    public function handle(GhlMarketingService $ghlMarketingService): void
    {
        $ghlMarketingService->syncApplicationEvent($this->user, $this->event);
    }
}
