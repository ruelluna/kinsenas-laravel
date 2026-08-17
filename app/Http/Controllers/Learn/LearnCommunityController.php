<?php

namespace App\Http\Controllers\Learn;

use App\Enums\CommunityPostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Learn\StoreCommunityPostReportRequest;
use App\Http\Requests\Learn\StoreCommunityPostRequest;
use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use App\Services\Content\CommunityPostService;
use App\Services\Content\CommunityReportService;
use App\Services\Content\LearnAccessService;
use App\Support\Content\CommunityPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class LearnCommunityController extends Controller
{
    public function __construct(
        private LearnAccessService $learnAccessService,
        private CommunityPostService $communityPostService,
        private CommunityReportService $communityReportService,
    ) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user() !== null, 403);

        $hasFullAccess = $this->learnAccessService->userHasFullLearnAccess($request->user());
        $categorySlug = $request->string('category')->toString();

        $categories = CommunityCategory::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (CommunityCategory $category) => CommunityPresenter::categorySummary($category));

        $posts = $hasFullAccess
            ? CommunityPost::query()
                ->with(['categories', 'author'])
                ->published()
                ->when(
                    filled($categorySlug),
                    fn ($query) => $query->whereHas(
                        'categories',
                        fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug)->published(),
                    ),
                )
                ->orderByDesc('published_at')
                ->paginate(12)
                ->withQueryString()
                ->through(fn (CommunityPost $post) => CommunityPresenter::postSummary($post))
            : new LengthAwarePaginator([], 0, 12);

        return Inertia::render('learn/community/index', [
            'hasFullAccess' => $hasFullAccess,
            'isAuthenticated' => true,
            'categories' => $categories,
            'activeCategory' => $categorySlug ?: null,
            'posts' => $posts,
        ]);
    }

    public function mine(Request $request): Response
    {
        $user = $request->user();

        abort_if($user === null, 403);

        $posts = CommunityPost::query()
            ->with(['categories', 'author'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(12)
            ->through(function (CommunityPost $post) {
                $summary = CommunityPresenter::postSummary($post);
                $summary['isOwnPost'] = true;

                return $summary;
            });

        return Inertia::render('learn/community/mine', [
            'hasFullAccess' => $this->learnAccessService->userHasFullLearnAccess($user),
            'isAuthenticated' => true,
            'posts' => $posts,
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($this->learnAccessService->userHasFullLearnAccess($request->user()), 403);

        $categories = CommunityCategory::query()
            ->published()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (CommunityCategory $category) => CommunityPresenter::categorySummary($category));

        return Inertia::render('learn/community/create', [
            'categories' => $categories,
            'hasFullAccess' => true,
            'isAuthenticated' => true,
        ]);
    }

    public function store(StoreCommunityPostRequest $request): RedirectResponse
    {
        abort_unless($this->learnAccessService->userHasFullLearnAccess($request->user()), 403);

        $post = $this->communityPostService->create($request->user(), $request->validated());

        return redirect()
            ->route('learn.community.mine')
            ->with('toast', ['type' => 'success', 'message' => 'Your story was submitted for review.']);
    }

    public function show(Request $request, CommunityPost $communityPost): Response
    {
        $user = $request->user();

        abort_if($user === null, 403);

        $hasFullAccess = $this->learnAccessService->userHasFullLearnAccess($user);
        $isOwner = $communityPost->isOwnedBy($user);

        if ($communityPost->status === CommunityPostStatus::Published) {
            abort_unless($hasFullAccess, 403);
        } elseif ($isOwner) {
            // Owner can view own pending/rejected posts.
        } else {
            abort(404);
        }

        $communityPost->load(['categories', 'author']);

        $summary = CommunityPresenter::postSummary($communityPost, includeBody: true);
        $summary['isOwnPost'] = $isOwner;

        return Inertia::render('learn/community/show', [
            'post' => $summary,
            'hasFullAccess' => $hasFullAccess,
            'isAuthenticated' => true,
            'canReport' => $hasFullAccess && $communityPost->status === CommunityPostStatus::Published && ! $isOwner,
        ]);
    }

    public function report(StoreCommunityPostReportRequest $request, CommunityPost $communityPost): RedirectResponse
    {
        abort_unless($this->learnAccessService->userHasFullLearnAccess($request->user()), 403);
        abort_unless($communityPost->status === CommunityPostStatus::Published, 404);

        $this->communityReportService->report($communityPost, $request->user(), $request->validated());

        return back()->with('toast', ['type' => 'success', 'message' => 'Report submitted. Thank you.']);
    }
}
