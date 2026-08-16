<?php

namespace App\Http\Controllers\Learn;

use App\Enums\ContentPostStatus;
use App\Http\Controllers\Controller;
use App\Models\PodcastEpisode;
use App\Models\PodcastShow;
use App\Services\Content\LearnAccessService;
use App\Support\Content\LearnLibraryPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnPodcastController extends Controller
{
    public function __construct(private LearnAccessService $learnAccessService) {}

    public function index(Request $request): Response
    {
        $hasFullAccess = $this->learnAccessService->userHasFullLearnAccess($request->user());

        $shows = PodcastShow::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (PodcastShow $show) => LearnLibraryPresenter::podcastShowSummary($show));

        return Inertia::render('learn/podcasts/index', [
            'hasFullAccess' => $hasFullAccess,
            'isAuthenticated' => $request->user() !== null,
            'shows' => $shows,
        ]);
    }

    public function show(Request $request, PodcastShow $podcastShow): Response
    {
        abort_unless($podcastShow->status === ContentPostStatus::Published, 404);

        $hasFullAccess = $this->learnAccessService->userHasFullLearnAccess($request->user());

        $episodesQuery = $podcastShow->episodes()
            ->when(
                $hasFullAccess,
                fn ($query) => $query->memberVisible(),
                fn ($query) => $query->publicTeaser(),
            )
            ->orderBy('episode_number');

        return Inertia::render('learn/podcasts/show', [
            'show' => LearnLibraryPresenter::podcastShowSummary($podcastShow),
            'hasFullAccess' => $hasFullAccess,
            'isAuthenticated' => $request->user() !== null,
            'episodes' => $episodesQuery->get()->map(
                fn (PodcastEpisode $episode) => LearnLibraryPresenter::podcastEpisodeSummary($episode),
            ),
        ]);
    }

    public function showEpisode(Request $request, PodcastShow $podcastShow, PodcastEpisode $podcastEpisode): Response
    {
        abort_unless($podcastShow->status === ContentPostStatus::Published, 404);
        abort_unless($podcastEpisode->podcast_show_id === $podcastShow->id, 404);
        abort_unless($podcastEpisode->status === ContentPostStatus::Published, 404);

        $hasFullAccess = $this->learnAccessService->userHasFullLearnAccess($request->user());

        if (! $hasFullAccess && ! $podcastEpisode->isPublicTeaser()) {
            abort(404);
        }

        $podcastEpisode->setRelation('show', $podcastShow);

        return Inertia::render('learn/podcasts/episode', [
            'episode' => LearnLibraryPresenter::podcastEpisodeSummary($podcastEpisode, includeShowNotes: $hasFullAccess),
            'showFullBody' => $hasFullAccess,
            'hasFullAccess' => $hasFullAccess,
            'isAuthenticated' => $request->user() !== null,
            'openGraph' => $podcastEpisode->isPublicTeaser()
                ? [
                    'title' => $podcastEpisode->title,
                    'description' => $podcastEpisode->excerpt ?? '',
                    'url' => route('learn.podcasts.episodes.show', [$podcastShow, $podcastEpisode], absolute: true),
                    'image' => $podcastShow->cover_image_url,
                ]
                : null,
        ]);
    }
}
