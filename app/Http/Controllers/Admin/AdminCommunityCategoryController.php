<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCommunityCategoryRequest;
use App\Http\Requests\Admin\UpdateCommunityCategoryRequest;
use App\Models\CommunityCategory;
use App\Services\Content\CommunityPublishService;
use App\Support\Content\CommunityPresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminCommunityCategoryController extends Controller
{
    public function __construct(private CommunityPublishService $publishService) {}

    public function index(): Response
    {
        $categories = CommunityCategory::query()
            ->withCount('posts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (CommunityCategory $category) => CommunityPresenter::categoryAdmin($category));

        return Inertia::render('admin/content/community-categories/index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/content/community-categories/create');
    }

    public function store(StoreCommunityCategoryRequest $request): RedirectResponse
    {
        $category = $this->publishService->createCategory($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category created.')]);

        return to_route('admin.content.community-categories.edit', $category);
    }

    public function edit(CommunityCategory $communityCategory): Response
    {
        return Inertia::render('admin/content/community-categories/edit', [
            'category' => CommunityPresenter::categoryAdmin($communityCategory->loadCount('posts')),
        ]);
    }

    public function update(UpdateCommunityCategoryRequest $request, CommunityCategory $communityCategory): RedirectResponse
    {
        $this->publishService->updateCategory($communityCategory, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category updated.')]);

        return back();
    }

    public function destroy(CommunityCategory $communityCategory): RedirectResponse
    {
        $communityCategory->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category deleted.')]);

        return to_route('admin.content.community-categories.index');
    }
}
