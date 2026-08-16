<?php

namespace App\Http\Controllers\Learn;

use App\Enums\ContentPostStatus;
use App\Http\Controllers\Controller;
use App\Models\SideHustle;
use App\Services\Content\LearnAccessService;
use App\Support\Content\LearnLibraryPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnSideHustleController extends Controller
{
    public function __construct(private LearnAccessService $learnAccessService) {}

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('learn.index', array_filter([
            'filter' => 'side-hustles',
            'category' => $request->string('category')->toString() ?: null,
        ]));
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
