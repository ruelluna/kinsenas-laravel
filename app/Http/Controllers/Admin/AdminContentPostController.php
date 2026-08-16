<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreContentPostRequest;
use App\Http\Requests\Admin\UpdateContentPostRequest;
use App\Models\ContentPost;
use App\Models\ContentSeries;
use App\Services\Content\ContentEngagementService;
use App\Services\Content\ContentPublishService;
use App\Support\Content\ContentPresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminContentPostController extends Controller
{
    public function __construct(
        private ContentPublishService $publishService,
        private ContentEngagementService $engagementService,
    ) {}

    public function index(): Response
    {
        $posts = ContentPost::query()
            ->with(['series', 'author'])
            ->latest('updated_at')
            ->paginate(25)
            ->through(function (ContentPost $post) {
                return [
                    ...ContentPresenter::postAdmin($post),
                    'helpfulCount' => $this->engagementService->helpfulCount($post),
                ];
            });

        return Inertia::render('admin/content/posts/index', [
            'posts' => $posts,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/content/posts/create', [
            'seriesOptions' => $this->seriesOptions(),
        ]);
    }

    public function store(StoreContentPostRequest $request): RedirectResponse
    {
        $post = $this->publishService->createPost($request->validated(), $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Post created.')]);

        return to_route('admin.content.posts.edit', $post);
    }

    public function edit(ContentPost $post): Response
    {
        return Inertia::render('admin/content/posts/edit', [
            'post' => ContentPresenter::postAdmin($post->load(['series', 'author'])),
            'seriesOptions' => $this->seriesOptions(),
        ]);
    }

    public function update(UpdateContentPostRequest $request, ContentPost $post): RedirectResponse
    {
        $this->publishService->updatePost($post, $request->validated(), $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Post updated.')]);

        return back();
    }

    public function destroy(ContentPost $post): RedirectResponse
    {
        $post->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Post deleted.')]);

        return to_route('admin.content.posts.index');
    }

    /**
     * @return list<array{id: string, title: string}>
     */
    private function seriesOptions(): array
    {
        return ContentSeries::query()
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (ContentSeries $series) => [
                'id' => $series->id,
                'title' => $series->title,
            ])
            ->all();
    }
}
