<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreContentPostCategoryRequest;
use App\Http\Requests\Admin\UpdateContentPostCategoryRequest;
use App\Models\ContentPostCategory;
use App\Services\Content\ContentPostCategoryPublishService;
use App\Support\Content\ContentPresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminContentPostCategoryController extends Controller
{
    public function __construct(private ContentPostCategoryPublishService $publishService) {}

    public function index(): Response
    {
        $categories = ContentPostCategory::query()
            ->withCount('posts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ContentPostCategory $category) => ContentPresenter::postCategoryAdmin($category));

        return Inertia::render('admin/content/post-categories/index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/content/post-categories/create');
    }

    public function store(StoreContentPostCategoryRequest $request): RedirectResponse
    {
        $category = $this->publishService->createCategory($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category created.')]);

        return to_route('admin.content.post-categories.edit', $category);
    }

    public function edit(ContentPostCategory $postCategory): Response
    {
        return Inertia::render('admin/content/post-categories/edit', [
            'category' => ContentPresenter::postCategoryAdmin($postCategory->loadCount('posts')),
        ]);
    }

    public function update(UpdateContentPostCategoryRequest $request, ContentPostCategory $postCategory): RedirectResponse
    {
        $this->publishService->updateCategory($postCategory, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category updated.')]);

        return back();
    }

    public function destroy(ContentPostCategory $postCategory): RedirectResponse
    {
        $postCategory->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category deleted.')]);

        return to_route('admin.content.post-categories.index');
    }
}
