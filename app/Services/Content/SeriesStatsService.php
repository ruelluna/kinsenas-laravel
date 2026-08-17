<?php

namespace App\Services\Content;

use App\Enums\ContentEngagementEventType;
use App\Models\ContentEngagementEvent;
use App\Models\ContentSeries;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SeriesStatsService
{
    /**
     * @return array{seriesCount: int, totalEpisodes: int}
     */
    public function summary(?int $days = null): array
    {
        $seriesCount = ContentSeries::query()->count();
        $totalEpisodes = ContentSeries::query()->withCount('posts')->get()->sum('posts_count');

        return [
            'seriesCount' => $seriesCount,
            'totalEpisodes' => $totalEpisodes,
        ];
    }

    /**
     * @return Collection<int, array{series: ContentSeries, postsCount: int, views: int}>
     */
    public function seriesWithViews(int $limit = 10, ?int $days = null): Collection
    {
        $since = $days !== null ? Carbon::now()->subDays($days) : null;

        return ContentSeries::query()
            ->withCount('posts')
            ->orderBy('title')
            ->limit($limit)
            ->get()
            ->map(function (ContentSeries $series) use ($since) {
                $viewsQuery = ContentEngagementEvent::query()
                    ->where('event_type', ContentEngagementEventType::Viewed)
                    ->whereHas('post', fn ($q) => $q->where('content_series_id', $series->id));

                if ($since !== null) {
                    $viewsQuery->where('created_at', '>=', $since);
                }

                return [
                    'series' => $series,
                    'postsCount' => $series->posts_count,
                    'views' => $viewsQuery->count(),
                ];
            })
            ->sortByDesc('views')
            ->values();
    }
}
