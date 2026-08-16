<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSideHustleCategoryRequest;
use App\Http\Requests\Admin\UpdateSideHustleCategoryRequest;
use App\Models\SideHustleCategory;
use App\Services\Content\LearnLibraryPublishService;
use App\Support\Content\LearnLibraryPresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminSideHustleCategoryController extends Controller
{
    public function __construct(private LearnLibraryPublishService $publishService) {}

    public function index(): Response
    {
        $categories = SideHustleCategory::query()
            ->withCount('sideHustles')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (SideHustleCategory $category) => LearnLibraryPresenter::categoryAdmin($category));

        return Inertia::render('admin/content/side-hustle-categories/index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/content/side-hustle-categories/create');
    }

    public function store(StoreSideHustleCategoryRequest $request): RedirectResponse
    {
        $category = $this->publishService->createCategory($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category created.')]);

        return to_route('admin.content.side-hustle-categories.edit', $category);
    }

    public function edit(SideHustleCategory $sideHustleCategory): Response
    {
        return Inertia::render('admin/content/side-hustle-categories/edit', [
            'category' => LearnLibraryPresenter::categoryAdmin($sideHustleCategory->loadCount('sideHustles')),
        ]);
    }

    public function update(UpdateSideHustleCategoryRequest $request, SideHustleCategory $sideHustleCategory): RedirectResponse
    {
        $this->publishService->updateCategory($sideHustleCategory, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category updated.')]);

        return back();
    }

    public function destroy(SideHustleCategory $sideHustleCategory): RedirectResponse
    {
        $sideHustleCategory->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category deleted.')]);

        return to_route('admin.content.side-hustle-categories.index');
    }
}
