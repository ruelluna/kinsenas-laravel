<?php

namespace App\Jobs;

use App\Models\SurveyResponse;
use App\Services\Marketing\GhlMarketingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncSurveyResponseToGhl implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SurveyResponse $surveyResponse,
    ) {}

    public function handle(GhlMarketingService $ghlMarketingService): void
    {
        $ghlMarketingService->syncSurveyResponse($this->surveyResponse);
    }
}
