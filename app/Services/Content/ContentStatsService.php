<?php

namespace App\Services\Content;

use App\Enums\ContentEngagementEventType;
use App\Enums\ContentReactionType;
use App\Models\ContentEngagementEvent;
use App\Models\ContentPost;
use App\Models\ContentReaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContentStatsService
{
    public function summary(?int $days = null): array
    {
        $since = $days !== null ? Carbon::now()->subDays($days) : null;

        $viewsQuery = ContentEngagementEvent::query()
            ->where('event_type', ContentEngagementEventType::Viewed);

        if ($since !== null) {
            $viewsQuery->where('created_at', '>=', $since);
        }

        $totalViews = (clone $viewsQuery)->count();
        $uniqueViewers = (clone $viewsQuery)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        $reactionsQuery = ContentReaction::query()
            ->where('reaction_type', ContentReactionType::Helpful);

        if ($since !== null) {
            $reactionsQuery->where('created_at', '>=', $since);
        }

        return [
            'totalViews' => $totalViews,
            'uniqueViewers' => $uniqueViewers,
            'totalReactions' => $reactionsQuery->count(),
        ];
    }

    /**
     * @return Collection<int, array{post: ContentPost, views: int, uniqueViewers: int, reactions: int}>
     */
    public function topPosts(int $limit = 10, ?int $days = null): Collection
    {
        $since = $days !== null ? Carbon::now()->subDays($days) : null;

        $viewsSub = ContentEngagementEvent::query()
            ->select('content_post_id', DB::raw('COUNT(*) as view_count'), DB::raw('COUNT(DISTINCT user_id) as unique_viewers'))
            ->where('event_type', ContentEngagementEventType::Viewed)
            ->when($since !== null, fn ($q) => $q->where('created_at', '>=', $since))
            ->groupBy('content_post_id');

        $reactionsSub = ContentReaction::query()
            ->select('content_post_id', DB::raw('COUNT(*) as reaction_count'))
            ->where('reaction_type', ContentReactionType::Helpful)
            ->when($since !== null, fn ($q) => $q->where('created_at', '>=', $since))
            ->groupBy('content_post_id');

        $posts = ContentPost::query()
            ->leftJoinSub($viewsSub, 'view_stats', 'view_stats.content_post_id', '=', 'content_posts.id')
            ->leftJoinSub($reactionsSub, 'reaction_stats', 'reaction_stats.content_post_id', '=', 'content_posts.id')
            ->select('content_posts.*')
            ->selectRaw('COALESCE(view_stats.view_count, 0) as stats_views')
            ->selectRaw('COALESCE(view_stats.unique_viewers, 0) as stats_unique_viewers')
            ->selectRaw('COALESCE(reaction_stats.reaction_count, 0) as stats_reactions')
            ->orderByDesc('stats_views')
            ->limit($limit)
            ->get();

        return $posts->map(fn (ContentPost $post) => [
            'post' => $post,
            'views' => (int) $post->stats_views,
            'uniqueViewers' => (int) $post->stats_unique_viewers,
            'reactions' => (int) $post->stats_reactions,
        ]);
    }
}
