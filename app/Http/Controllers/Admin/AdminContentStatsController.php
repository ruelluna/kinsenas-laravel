<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Content\ContentStatsService;
use App\Support\Content\ContentPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminContentStatsController extends Controller
{
    public function __construct(private ContentStatsService $statsService) {}

    public function __invoke(Request $request): Response
    {
        $days = match ($request->string('window')->toString()) {
            '7' => 7,
            '30' => 30,
            default => null,
        };

        $topPosts = $this->statsService->topPosts(10, $days)->map(fn (array $row) => [
            'post' => ContentPresenter::postSummary($row['post']),
            'views' => $row['views'],
            'uniqueViewers' => $row['uniqueViewers'],
            'reactions' => $row['reactions'],
        ]);

        return Inertia::render('admin/content/posts/stats', [
            'window' => $request->string('window')->toString() ?: 'all',
            'summary' => $this->statsService->summary($days),
            'topPosts' => $topPosts,
        ]);
    }
}
