<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use App\Models\CommunityPostReport;
use App\Support\Content\CommunityPresenter;
use Inertia\Inertia;
use Inertia\Response;

class AdminCommunitySettingsController extends Controller
{
    public function index(): Response
    {
        $categories = CommunityCategory::query()
            ->withCount('posts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (CommunityCategory $category) => CommunityPresenter::categoryAdmin($category));

        $pendingPosts = CommunityPost::query()
            ->with(['categories', 'author'])
            ->pending()
            ->orderBy('created_at')
            ->limit(20)
            ->get()
            ->map(fn (CommunityPost $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'authorName' => $post->author?->name,
                'categoryNames' => $post->categories->pluck('name')->join(', '),
            ]);

        $reports = CommunityPostReport::query()
            ->with(['post', 'reporter'])
            ->where('status', 'open')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn (CommunityPostReport $report) => [
                'id' => $report->id,
                'reasonLabel' => $report->reason->label(),
                'details' => $report->details,
                'postTitle' => $report->post?->title,
                'postSlug' => $report->post?->slug,
                'reporterName' => $report->reporter?->name,
            ]);

        return Inertia::render('admin/content/community/settings', [
            'categories' => $categories,
            'pendingPosts' => $pendingPosts,
            'reports' => $reports,
        ]);
    }
}
