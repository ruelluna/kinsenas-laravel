<?php

namespace App\Http\Controllers\Learn;

use App\Enums\ContentPostType;
use App\Enums\ContentSeriesStatus;
use App\Http\Controllers\Controller;
use App\Models\ContentPost;
use App\Models\ContentSeries;
use App\Services\Content\LearnAccessService;
use App\Support\Content\ContentPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnIndexController extends Controller
{
    public function __construct(private LearnAccessService $learnAccessService) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $hasFullAccess = $this->learnAccessService->userHasFullLearnAccess($user);
        $filter = $request->string('filter')->toString() ?: 'all';

        if ($hasFullAccess) {
            $postsQuery = ContentPost::query()
                ->with(['series', 'author'])
                ->memberVisible()
                ->latest('published_at');

            if ($filter === 'reminders') {
                $postsQuery->where('content_type', ContentPostType::Reminder);
            } elseif ($filter === 'articles') {
                $postsQuery->where('content_type', ContentPostType::Article);
            } elseif ($filter === 'series') {
                $postsQuery->whereNotNull('content_series_id');
            }

            $posts = $postsQuery->paginate(12)->withQueryString();

            $series = ContentSeries::query()
                ->where('status', ContentSeriesStatus::Published)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get()
                ->map(fn (ContentSeries $item) => ContentPresenter::seriesSummary($item));
        } else {
            $posts = ContentPost::query()
                ->with(['series', 'author'])
                ->publicTeaser()
                ->latest('published_at')
                ->paginate(12)
                ->withQueryString();

            $series = ContentSeries::query()
                ->where('status', ContentSeriesStatus::Published)
                ->orderBy('sort_order')
                ->get()
                ->filter(fn (ContentSeries $item) => $item->hasPublicTeaserPosts())
                ->values()
                ->map(fn (ContentSeries $item) => ContentPresenter::seriesSummary($item));
        }

        return Inertia::render('learn/index', [
            'hasFullAccess' => $hasFullAccess,
            'filter' => $filter,
            'posts' => $posts->through(fn (ContentPost $post) => ContentPresenter::postSummary($post)),
            'series' => $series,
            'isAuthenticated' => $user !== null,
        ]);
    }
}
