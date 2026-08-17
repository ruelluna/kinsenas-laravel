<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePodcastShowRequest;
use App\Http\Requests\Admin\UpdatePodcastShowRequest;
use App\Models\PodcastEpisode;
use App\Models\PodcastShow;
use App\Services\Content\LearnLibraryPublishService;
use App\Services\Content\PodcastStatsService;
use App\Support\Content\LearnLibraryPresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminPodcastShowController extends Controller
{
    public function __construct(private LearnLibraryPublishService $publishService) {}

    public function index(): Response
    {
        $shows = PodcastShow::query()
            ->withCount('episodes')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (PodcastShow $show) => LearnLibraryPresenter::podcastShowAdmin($show));

        return Inertia::render('admin/content/podcast-shows/index', [
            'shows' => $shows,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/content/podcast-shows/create');
    }

    public function store(StorePodcastShowRequest $request): RedirectResponse
    {
        $show = $this->publishService->createPodcastShow($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Podcast show created.')]);

        return to_route('admin.content.podcast-shows.edit', $show);
    }

    public function edit(PodcastShow $podcastShow): Response
    {
        $episodes = $podcastShow->episodes()
            ->orderBy('episode_number')
            ->get()
            ->map(fn (PodcastEpisode $episode) => LearnLibraryPresenter::podcastEpisodeAdmin($episode));

        return Inertia::render('admin/content/podcast-shows/edit', [
            'show' => LearnLibraryPresenter::podcastShowAdmin($podcastShow->loadCount('episodes')),
            'episodes' => $episodes,
        ]);
    }

    public function update(UpdatePodcastShowRequest $request, PodcastShow $podcastShow): RedirectResponse
    {
        $this->publishService->updatePodcastShow($podcastShow, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Podcast show updated.')]);

        return back();
    }

    public function destroy(PodcastShow $podcastShow): RedirectResponse
    {
        $podcastShow->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Podcast show deleted.')]);

        return to_route('admin.content.podcasts.index');
    }

    public function settings(): Response
    {
        return Inertia::render('admin/content/podcasts/settings');
    }

    public function stats(): Response
    {
        $statsService = app(PodcastStatsService::class);

        return Inertia::render('admin/content/podcasts/stats', [
            'summary' => $statsService->summary(),
        ]);
    }
}
