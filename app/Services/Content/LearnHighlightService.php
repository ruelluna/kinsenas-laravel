<?php

namespace App\Services\Content;

use App\Models\ContentPost;
use App\Support\Content\ContentPresenter;

class LearnHighlightService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function recentPosts(int $limit = 3): array
    {
        return ContentPost::query()
            ->memberVisible()
            ->with(['series', 'author'])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (ContentPost $post) => ContentPresenter::postSummary($post))
            ->all();
    }
}
