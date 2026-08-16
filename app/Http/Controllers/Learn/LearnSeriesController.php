<?php

namespace App\Http\Controllers\Learn;

use App\Enums\ContentPostStatus;
use App\Enums\ContentSeriesStatus;
use App\Http\Controllers\Controller;
use App\Models\ContentSeries;
use App\Services\Content\ContentEngagementService;
use App\Services\Content\LearnAccessService;
use App\Support\Content\ContentPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnSeriesController extends Controller
{
    public function __construct(
        private LearnAccessService $learnAccessService,
        private ContentEngagementService $engagementService,
    ) {}

    public function show(Request $request, ContentSeries $series): Response
    {
        abort_unless($series->status === ContentSeriesStatus::Published, 404);

        $user = $request->user();
        $hasFullAccess = $this->learnAccessService->userHasFullLearnAccess($user);

        if (! $hasFullAccess && ! $series->hasPublicTeaserPosts()) {
            abort(404);
        }

        $episodesQuery = $series->posts()
            ->with('author')
            ->orderBy('episode_number');

        if ($hasFullAccess) {
            $episodesQuery->where('status', ContentPostStatus::Published);
        } else {
            $episodesQuery->publicTeaser();
        }

        $episodes = $episodesQuery->get();

        if ($episodes->isEmpty()) {
            abort(404);
        }

        $viewedIds = $user !== null
            ? $this->engagementService->viewedPostIdsForUser($user)
            : [];

        return Inertia::render('learn/series/show', [
            'hasFullAccess' => $hasFullAccess,
            'series' => ContentPresenter::seriesSummary($series),
            'episodes' => $episodes->map(fn ($post) => [
                ...ContentPresenter::postSummary($post),
                'isRead' => in_array($post->id, $viewedIds, true),
            ]),
            'isAuthenticated' => $user !== null,
        ]);
    }
}
