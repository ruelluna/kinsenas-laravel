<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreContentSeriesRequest;
use App\Http\Requests\Admin\UpdateContentSeriesRequest;
use App\Models\ContentSeries;
use App\Services\Content\ContentPublishService;
use App\Services\Content\SeriesStatsService;
use App\Support\Content\ContentPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminContentSeriesController extends Controller
{
    public function __construct(private ContentPublishService $publishService) {}

    public function index(): Response
    {
        $series = ContentSeries::query()
            ->withCount('posts')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (ContentSeries $item) => ContentPresenter::seriesAdmin($item));

        return Inertia::render('admin/content/series/index', [
            'series' => $series,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/content/series/create');
    }

    public function store(StoreContentSeriesRequest $request): RedirectResponse
    {
        $series = $this->publishService->createSeries($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Series created.')]);

        return to_route('admin.content.series.edit', $series);
    }

    public function edit(ContentSeries $series): Response
    {
        return Inertia::render('admin/content/series/edit', [
            'series' => ContentPresenter::seriesAdmin($series->loadCount('posts')),
        ]);
    }

    public function update(UpdateContentSeriesRequest $request, ContentSeries $series): RedirectResponse
    {
        $this->publishService->updateSeries($series, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Series updated.')]);

        return back();
    }

    public function destroy(ContentSeries $series): RedirectResponse
    {
        $series->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Series deleted.')]);

        return to_route('admin.content.series.index');
    }

    public function settings(): Response
    {
        return Inertia::render('admin/content/series/settings');
    }

    public function stats(Request $request): Response
    {
        $days = match ($request->string('window')->toString()) {
            '7' => 7,
            '30' => 30,
            default => null,
        };

        $statsService = app(SeriesStatsService::class);

        $seriesRows = $statsService->seriesWithViews(20, $days)->map(fn (array $row) => [
            'series' => ContentPresenter::seriesAdmin($row['series']),
            'postsCount' => $row['postsCount'],
            'views' => $row['views'],
        ]);

        return Inertia::render('admin/content/series/stats', [
            'window' => $request->string('window')->toString() ?: 'all',
            'summary' => $statsService->summary($days),
            'seriesRows' => $seriesRows,
        ]);
    }
}
