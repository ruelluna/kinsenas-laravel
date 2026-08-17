<?php

namespace App\Services\Content;

use App\Enums\ContentPostStatus;
use App\Models\PodcastEpisode;
use App\Models\PodcastShow;

class PodcastStatsService
{
    /**
     * @return array{showCount: int, episodeCount: int, publishedShows: int, publishedEpisodes: int}
     */
    public function summary(): array
    {
        return [
            'showCount' => PodcastShow::query()->count(),
            'episodeCount' => PodcastEpisode::query()->count(),
            'publishedShows' => PodcastShow::query()->where('status', ContentPostStatus::Published)->count(),
            'publishedEpisodes' => PodcastEpisode::query()->where('status', ContentPostStatus::Published)->count(),
        ];
    }
}
