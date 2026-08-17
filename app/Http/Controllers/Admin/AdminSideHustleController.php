<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSideHustleRequest;
use App\Http\Requests\Admin\UpdateSideHustleRequest;
use App\Models\SideHustle;
use App\Models\SideHustleCategory;
use App\Services\Content\LearnLibraryPublishService;
use App\Services\Content\SideHustleStatsService;
use App\Support\Content\LearnLibraryPresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminSideHustleController extends Controller
{
    public function __construct(private LearnLibraryPublishService $publishService) {}

    public function index(): Response
    {
        $hustles = SideHustle::query()
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(25)
            ->through(fn (SideHustle $hustle) => LearnLibraryPresenter::sideHustleAdmin($hustle));

        return Inertia::render('admin/content/side-hustles/index', [
            'hustles' => $hustles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/content/side-hustles/create', [
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function store(StoreSideHustleRequest $request): RedirectResponse
    {
        $hustle = $this->publishService->createSideHustle($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Side hustle created.')]);

        return to_route('admin.content.side-hustles.edit', $hustle);
    }

    public function edit(SideHustle $sideHustle): Response
    {
        return Inertia::render('admin/content/side-hustles/edit', [
            'hustle' => LearnLibraryPresenter::sideHustleAdmin($sideHustle->load('category')),
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function update(UpdateSideHustleRequest $request, SideHustle $sideHustle): RedirectResponse
    {
        $this->publishService->updateSideHustle($sideHustle, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Side hustle updated.')]);

        return back();
    }

    public function destroy(SideHustle $sideHustle): RedirectResponse
    {
        $sideHustle->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Side hustle deleted.')]);

        return to_route('admin.content.side-hustles.index');
    }

    public function settings(): Response
    {
        $categories = SideHustleCategory::query()
            ->withCount('sideHustles')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (SideHustleCategory $category) => LearnLibraryPresenter::categoryAdmin($category));

        return Inertia::render('admin/content/side-hustles/settings', [
            'categories' => $categories,
        ]);
    }

    public function stats(): Response
    {
        $statsService = app(SideHustleStatsService::class);

        return Inertia::render('admin/content/side-hustles/stats', [
            'summary' => $statsService->summary(),
            'byCategory' => $statsService->byCategory(),
        ]);
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function categoryOptions(): array
    {
        return SideHustleCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (SideHustleCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
    }
}
