<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePodcastEpisodeRequest;
use App\Http\Requests\Admin\UpdatePodcastEpisodeRequest;
use App\Models\PodcastEpisode;
use App\Models\PodcastShow;
use App\Services\Content\LearnLibraryPublishService;
use App\Support\Content\LearnLibraryPresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminPodcastEpisodeController extends Controller
{
    public function __construct(private LearnLibraryPublishService $publishService) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('admin.content.podcasts.index');
    }

    public function create(): Response|RedirectResponse
    {
        return redirect()->route('admin.content.podcasts.index');
    }

    public function createForShow(PodcastShow $podcastShow): Response
    {
        return Inertia::render('admin/content/podcast-episodes/create', [
            'showOptions' => $this->showOptions(),
            'selectedShowId' => $podcastShow->id,
            'parentShow' => LearnLibraryPresenter::podcastShowAdmin($podcastShow),
            'storeUrl' => route('admin.content.podcasts.episodes.store', $podcastShow),
        ]);
    }

    public function store(StorePodcastEpisodeRequest $request): RedirectResponse
    {
        return redirect()->route('admin.content.podcasts.index');
    }

    public function storeForShow(StorePodcastEpisodeRequest $request, PodcastShow $podcastShow): RedirectResponse
    {
        $episode = $this->publishService->createPodcastEpisode($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Podcast episode created.')]);

        return to_route('admin.content.podcast-episodes.edit', $episode);
    }

    public function edit(PodcastEpisode $podcastEpisode): Response
    {
        return Inertia::render('admin/content/podcast-episodes/edit', [
            'episode' => LearnLibraryPresenter::podcastEpisodeAdmin($podcastEpisode->load('show')),
            'showOptions' => $this->showOptions(),
        ]);
    }

    public function update(UpdatePodcastEpisodeRequest $request, PodcastEpisode $podcastEpisode): RedirectResponse
    {
        $this->publishService->updatePodcastEpisode($podcastEpisode, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Podcast episode updated.')]);

        return back();
    }

    public function destroy(PodcastEpisode $podcastEpisode): RedirectResponse
    {
        $podcastEpisode->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Podcast episode deleted.')]);

        return to_route('admin.content.podcast-shows.edit', $podcastEpisode->load('show')->show);
    }

    /**
     * @return list<array{id: string, title: string}>
     */
    private function showOptions(): array
    {
        return PodcastShow::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (PodcastShow $show) => [
                'id' => $show->id,
                'title' => $show->title,
            ])
            ->all();
    }
}
