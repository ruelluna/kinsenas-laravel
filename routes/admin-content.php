<?php

use App\Enums\PlatformPermission;
use App\Http\Controllers\Admin\AdminCommunityCategoryController;
use App\Http\Controllers\Admin\AdminCommunityModerationController;
use App\Http\Controllers\Admin\AdminCommunityPostController;
use App\Http\Controllers\Admin\AdminCommunityReportController;
use App\Http\Controllers\Admin\AdminCommunitySettingsController;
use App\Http\Controllers\Admin\AdminContentPostCategoryController;
use App\Http\Controllers\Admin\AdminContentPostController;
use App\Http\Controllers\Admin\AdminContentSeriesController;
use App\Http\Controllers\Admin\AdminContentStatsController;
use App\Http\Controllers\Admin\AdminContentUploadController;
use App\Http\Controllers\Admin\AdminPodcastEpisodeController;
use App\Http\Controllers\Admin\AdminPodcastShowController;
use App\Http\Controllers\Admin\AdminSideHustleCategoryController;
use App\Http\Controllers\Admin\AdminSideHustleController;
use App\Http\Controllers\Learn\LearnPostController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:'.PlatformPermission::ManageContent->value)->group(function (): void {
    Route::post('content/uploads', [AdminContentUploadController::class, 'store'])->name('content.uploads.store');
    Route::get('content/posts/{post}/preview', [LearnPostController::class, 'preview'])->name('content.posts.preview');
    Route::resource('content/posts', AdminContentPostController::class)->names('content.posts')->except(['show']);
    Route::resource('content/side-hustles', AdminSideHustleController::class)->names('content.side-hustles')->except(['show']);
});

Route::middleware('permission:'.PlatformPermission::ManagePlatform->value)->group(function (): void {
    Route::get('content/stats', fn () => redirect()->route('admin.content.posts.stats'))->name('content.stats');
    Route::get('content/post-categories', fn () => redirect()->route('admin.content.posts.settings'));
    Route::get('content/side-hustle-categories', fn () => redirect()->route('admin.content.side-hustles.settings'));
    Route::get('content/community-categories', fn () => redirect()->route('admin.content.community.settings'));
    Route::get('content/community-posts/pending', fn () => redirect()->route('admin.content.community.settings'))->name('content.community-posts.pending');
    Route::get('content/community-reports', fn () => redirect()->route('admin.content.community.settings'))->name('content.community-reports.index');
    Route::get('content/podcast-shows', fn () => redirect()->route('admin.content.podcasts.index'));
    Route::get('content/community-posts', fn () => redirect()->route('admin.content.community.index'))->name('content.community-posts.index');

    Route::get('content/posts/settings', [AdminContentPostController::class, 'settings'])->name('content.posts.settings');
    Route::get('content/posts/stats', AdminContentStatsController::class)->name('content.posts.stats');

    Route::get('content/series/settings', [AdminContentSeriesController::class, 'settings'])->name('content.series.settings');
    Route::get('content/series/stats', [AdminContentSeriesController::class, 'stats'])->name('content.series.stats');

    Route::get('content/podcasts', [AdminPodcastShowController::class, 'index'])->name('content.podcasts.index');
    Route::get('content/podcasts/settings', [AdminPodcastShowController::class, 'settings'])->name('content.podcasts.settings');
    Route::get('content/podcasts/stats', [AdminPodcastShowController::class, 'stats'])->name('content.podcasts.stats');
    Route::get('content/podcasts/{podcastShow}/episodes/create', [AdminPodcastEpisodeController::class, 'createForShow'])->name('content.podcasts.episodes.create');
    Route::post('content/podcasts/{podcastShow}/episodes', [AdminPodcastEpisodeController::class, 'storeForShow'])->name('content.podcasts.episodes.store');

    Route::get('content/side-hustles/settings', [AdminSideHustleController::class, 'settings'])->name('content.side-hustles.settings');
    Route::get('content/side-hustles/stats', [AdminSideHustleController::class, 'stats'])->name('content.side-hustles.stats');

    Route::get('content/community', [AdminCommunityPostController::class, 'index'])->name('content.community.index');
    Route::get('content/community/settings', [AdminCommunitySettingsController::class, 'index'])->name('content.community.settings');
    Route::get('content/community/stats', [AdminCommunityPostController::class, 'stats'])->name('content.community.stats');

    Route::delete('content/community-posts/{communityPost}', [AdminCommunityPostController::class, 'destroy'])->name('content.community-posts.destroy');
    Route::post('content/community-posts/{communityPost}/approve', [AdminCommunityModerationController::class, 'approve'])->name('content.community-posts.approve');
    Route::post('content/community-posts/{communityPost}/reject', [AdminCommunityModerationController::class, 'reject'])->name('content.community-posts.reject');
    Route::post('content/community-reports/{communityPostReport}/dismiss', [AdminCommunityReportController::class, 'dismiss'])->name('content.community-reports.dismiss');
    Route::post('content/community-reports/{communityPostReport}/resolve', [AdminCommunityReportController::class, 'resolve'])->name('content.community-reports.resolve');
    Route::resource('content/community-categories', AdminCommunityCategoryController::class)->names('content.community-categories')->except(['show']);
    Route::resource('content/post-categories', AdminContentPostCategoryController::class)->names('content.post-categories')->except(['show']);
    Route::resource('content/series', AdminContentSeriesController::class)->names('content.series')->except(['show']);
    Route::resource('content/side-hustle-categories', AdminSideHustleCategoryController::class)->names('content.side-hustle-categories')->except(['show']);
    Route::resource('content/podcast-shows', AdminPodcastShowController::class)->names('content.podcast-shows')->except(['show', 'index']);
    Route::get('content/podcast-shows', fn () => redirect()->route('admin.content.podcasts.index'));
    Route::resource('content/podcast-episodes', AdminPodcastEpisodeController::class)->names('content.podcast-episodes')->except(['show']);
});
