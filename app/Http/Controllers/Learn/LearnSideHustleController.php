<?php

namespace App\Http\Controllers\Learn;

use App\Enums\ContentPostStatus;
use App\Http\Controllers\Controller;
use App\Models\SideHustle;
use App\Models\SideHustleCategory;
use App\Services\Content\LearnAccessService;
use App\Support\Content\LearnLibraryPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnSideHustleController extends Controller
{
    public function __construct(private LearnAccessService $learnAccessService) {}

    public function index(Request $request): Response
    {
        $hasFullAccess = $this->learnAccessService->userHasFullLearnAccess($request->user());
        $categorySlug = $request->string('category')->toString();

        $categories = SideHustleCategory::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (SideHustleCategory $category) => LearnLibraryPresenter::categorySummary($category));

        $hustlesQuery = SideHustle::query()
            ->with('category')
            ->when(
                $hasFullAccess,
                fn ($query) => $query->memberVisible(),
                fn ($query) => $query->publicTeaser(),
            )
            ->when(
                filled($categorySlug),
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug)->published(),
                ),
            )
            ->orderBy('sort_order')
            ->orderBy('title');

        return Inertia::render('learn/side-hustles/index', [
            'hasFullAccess' => $hasFullAccess,
            'isAuthenticated' => $request->user() !== null,
            'categories' => $categories,
            'activeCategory' => $categorySlug ?: null,
            'hustles' => $hustlesQuery->paginate(12)->through(
                fn (SideHustle $hustle) => LearnLibraryPresenter::sideHustleSummary($hustle),
            ),
        ]);
    }

    public function show(Request $request, SideHustle $sideHustle): Response
    {
        abort_unless($sideHustle->status === ContentPostStatus::Published, 404);

        $hasFullAccess = $this->learnAccessService->userHasFullLearnAccess($request->user());

        if (! $hasFullAccess && ! $sideHustle->isPublicTeaser()) {
            abort(404);
        }

        $sideHustle->load('category');

        return Inertia::render('learn/side-hustles/show', [
            'hustle' => LearnLibraryPresenter::sideHustleSummary($sideHustle, includeBody: $hasFullAccess),
            'showFullBody' => $hasFullAccess,
            'hasFullAccess' => $hasFullAccess,
            'isAuthenticated' => $request->user() !== null,
            'openGraph' => $sideHustle->isPublicTeaser()
                ? [
                    'title' => $sideHustle->title,
                    'description' => $sideHustle->excerpt ?? '',
                    'url' => route('learn.side-hustles.show', $sideHustle, absolute: true),
                    'image' => $sideHustle->cover_image_url,
                ]
                : null,
        ]);
    }
}
