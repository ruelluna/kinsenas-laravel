<?php

namespace App\Http\Controllers\Learn;

use App\Enums\ContentPostType;
use App\Enums\ContentSeriesStatus;
use App\Http\Controllers\Controller;
use App\Models\ContentPost;
use App\Models\ContentSeries;
use App\Models\PodcastShow;
use App\Models\SideHustle;
use App\Models\SideHustleCategory;
use App\Services\Content\LearnAccessService;
use App\Support\Content\ContentPresenter;
use App\Support\Content\LearnLibraryPresenter;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class LearnIndexController extends Controller
{
    private const MEMBER_FILTERS = ['series', 'reminders', 'articles'];

    public function __construct(private LearnAccessService $learnAccessService) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $hasFullAccess = $this->learnAccessService->userHasFullLearnAccess($user);
        $filter = $request->string('filter')->toString() ?: 'all';
        $categorySlug = $request->string('category')->toString();

        if (! $hasFullAccess && in_array($filter, self::MEMBER_FILTERS, true)) {
            $filter = 'all';
        }

        $showPosts = in_array($filter, ['all', 'series', 'reminders', 'articles'], true);
        $showSeriesSection = in_array($filter, ['all', 'series'], true);
        $showPodcasts = in_array($filter, ['all', 'podcasts'], true);

        $posts = $showPosts
            ? $this->postsForFilter($filter, $hasFullAccess)
            : $this->emptyPostsPaginator();

        $series = $showSeriesSection
            ? $this->seriesForAccess($hasFullAccess)
            : collect();

        $categories = $filter === 'side-hustles'
            ? SideHustleCategory::query()
                ->published()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (SideHustleCategory $category) => LearnLibraryPresenter::categorySummary($category))
            : collect();

        $hustles = $filter === 'side-hustles'
            ? $this->hustlesForFilter($hasFullAccess, $categorySlug)
            : null;

        $hustlePreviews = $filter === 'all'
            ? $this->hustlePreviews($hasFullAccess)
            : [];

        $shows = $showPodcasts
            ? PodcastShow::query()
                ->published()
                ->orderBy('sort_order')
                ->orderBy('title')
                ->when($filter === 'all', fn ($query) => $query->limit(6))
                ->get()
                ->map(fn (PodcastShow $show) => LearnLibraryPresenter::podcastShowSummary($show))
            : collect();

        return Inertia::render('learn/index', [
            'hasFullAccess' => $hasFullAccess,
            'filter' => $filter,
            'activeCategory' => $categorySlug ?: null,
            'posts' => $posts,
            'series' => $series,
            'categories' => $categories,
            'hustles' => $hustles,
            'hustlePreviews' => $hustlePreviews,
            'shows' => $shows,
            'isAuthenticated' => $user !== null,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, array<string, mixed>>
     */
    private function postsForFilter(string $filter, bool $hasFullAccess): LengthAwarePaginator
    {
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

            return $postsQuery
                ->paginate(12)
                ->withQueryString()
                ->through(fn (ContentPost $post) => ContentPresenter::postSummary($post));
        }

        return ContentPost::query()
            ->with(['series', 'author'])
            ->publicTeaser()
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (ContentPost $post) => ContentPresenter::postSummary($post));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function seriesForAccess(bool $hasFullAccess): Collection
    {
        $query = ContentSeries::query()
            ->where('status', ContentSeriesStatus::Published)
            ->orderBy('sort_order')
            ->orderBy('title');

        if ($hasFullAccess) {
            return $query
                ->get()
                ->map(fn (ContentSeries $item) => ContentPresenter::seriesSummary($item));
        }

        return $query
            ->get()
            ->filter(fn (ContentSeries $item) => $item->hasPublicTeaserPosts())
            ->values()
            ->map(fn (ContentSeries $item) => ContentPresenter::seriesSummary($item));
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, array<string, mixed>>
     */
    private function hustlesForFilter(bool $hasFullAccess, string $categorySlug): LengthAwarePaginator
    {
        return SideHustle::query()
            ->with('category')
            ->when(
                $hasFullAccess,
                fn ($query) => $query->memberVisible(),
                fn ($query) => $query->publicTeaser(),
            )
            ->when(
                filled($categorySlug),
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug)->published(),
                ),
            )
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (SideHustle $hustle) => LearnLibraryPresenter::sideHustleSummary($hustle));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function hustlePreviews(bool $hasFullAccess): array
    {
        return SideHustle::query()
            ->with('category')
            ->when(
                $hasFullAccess,
                fn ($query) => $query->memberVisible(),
                fn ($query) => $query->publicTeaser(),
            )
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(6)
            ->get()
            ->map(fn (SideHustle $hustle) => LearnLibraryPresenter::sideHustleSummary($hustle))
            ->all();
    }

    /**
     * @return LengthAwarePaginator<int, never>
     */
    private function emptyPostsPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, 12);
    }
}
