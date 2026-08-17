<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Services\Content\CommunityModerationService;
use App\Services\Content\CommunityStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCommunityPostController extends Controller
{
    public function __construct(private CommunityModerationService $moderationService) {}

    public function index(): Response
    {
        $posts = CommunityPost::query()
            ->listedInAdmin()
            ->with(['categories', 'author'])
            ->orderByDesc('created_at')
            ->paginate(25)
            ->through(fn (CommunityPost $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status->value,
                'statusLabel' => $post->status->label(),
                'authorName' => $post->author?->name,
                'categoryNames' => $post->categories->pluck('name')->join(', '),
                'publishedAt' => $post->published_at?->toIso8601String(),
                'createdAt' => $post->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/content/community/index', [
            'posts' => $posts,
        ]);
    }

    public function stats(Request $request): Response
    {
        $days = match ($request->string('window')->toString()) {
            '7' => 7,
            '30' => 30,
            default => null,
        };

        $statsService = app(CommunityStatsService::class);

        return Inertia::render('admin/content/community/stats', [
            'window' => $request->string('window')->toString() ?: 'all',
            'summary' => $statsService->summary($days),
        ]);
    }

    public function destroy(Request $request, CommunityPost $communityPost): RedirectResponse
    {
        abort_unless($request->user()?->canManagePlatform(), 403);

        $this->moderationService->remove($communityPost, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Community post removed from the feed.']);

        return back();
    }
}
