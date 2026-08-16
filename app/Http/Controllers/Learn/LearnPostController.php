<?php

namespace App\Http\Controllers\Learn;

use App\Enums\ContentEngagementSource;
use App\Enums\ContentPostStatus;
use App\Http\Controllers\Controller;
use App\Models\ContentPost;
use App\Services\Content\ContentEngagementService;
use App\Services\Content\LearnAccessService;
use App\Support\Content\ContentPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnPostController extends Controller
{
    public function __construct(
        private LearnAccessService $learnAccessService,
        private ContentEngagementService $engagementService,
    ) {}

    public function show(Request $request, ContentPost $post): Response
    {
        abort_unless($post->status === ContentPostStatus::Published, 404);

        $user = $request->user();
        $hasFullAccess = $this->learnAccessService->userHasFullLearnAccess($user);

        if (! $hasFullAccess && ! $post->isPublicTeaser()) {
            abort(404);
        }

        $showFullBody = $hasFullAccess;

        if ($showFullBody) {
            $this->engagementService->recordView(
                $post,
                ContentEngagementSource::Internal,
                $user,
            );
        } else {
            $sessionHash = hash('sha256', $request->session()->getId());
            $this->engagementService->recordView(
                $post,
                ContentEngagementSource::External,
                null,
                $sessionHash,
            );
        }

        $neighbors = $this->seriesNeighbors($post);

        return Inertia::render('learn/posts/show', [
            'post' => ContentPresenter::postSummary($post->load(['series', 'author']), includeBody: $showFullBody),
            'showFullBody' => $showFullBody,
            'hasFullAccess' => $hasFullAccess,
            'helpfulCount' => $this->engagementService->helpfulCount($post),
            'hasReacted' => $this->engagementService->userHasReacted($post, $user),
            'previousEpisode' => $neighbors['previous'],
            'nextEpisode' => $neighbors['next'],
            'isAuthenticated' => $user !== null,
            'openGraph' => $post->isPublicTeaser()
                ? [
                    'title' => $post->title,
                    'description' => $post->excerpt ?? '',
                    'url' => route('learn.posts.show', $post, absolute: true),
                    'image' => $post->cover_image_url,
                ]
                : null,
        ]);
    }

    public function preview(Request $request, ContentPost $post): Response
    {
        abort_unless($request->user()?->canManageContentPost($post), 403);

        $post->load(['series', 'author']);

        return Inertia::render('learn/posts/show', [
            'post' => ContentPresenter::postSummary($post, includeBody: true),
            'showFullBody' => true,
            'hasFullAccess' => true,
            'helpfulCount' => $this->engagementService->helpfulCount($post),
            'hasReacted' => false,
            'previousEpisode' => null,
            'nextEpisode' => null,
            'isAuthenticated' => true,
            'isPreview' => true,
        ]);
    }

    /**
     * @return array{previous: array<string, mixed>|null, next: array<string, mixed>|null}
     */
    private function seriesNeighbors(ContentPost $post): array
    {
        if ($post->content_series_id === null || $post->episode_number === null) {
            return ['previous' => null, 'next' => null];
        }

        $previous = ContentPost::query()
            ->published()
            ->where('content_series_id', $post->content_series_id)
            ->where('episode_number', '<', $post->episode_number)
            ->orderByDesc('episode_number')
            ->first();

        $next = ContentPost::query()
            ->published()
            ->where('content_series_id', $post->content_series_id)
            ->where('episode_number', '>', $post->episode_number)
            ->orderBy('episode_number')
            ->first();

        return [
            'previous' => $previous ? ContentPresenter::postSummary($previous) : null,
            'next' => $next ? ContentPresenter::postSummary($next) : null,
        ];
    }
}
