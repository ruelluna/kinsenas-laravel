<?php

use App\Enums\PlatformPermission;
use App\Http\Controllers\Admin\AdminCommunityCategoryController;
use App\Http\Controllers\Admin\AdminCommunityModerationController;
use App\Http\Controllers\Admin\AdminCommunityReportController;
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
    Route::get('content/stats', AdminContentStatsController::class)->name('content.stats');
    Route::get('content/community-posts/pending', [AdminCommunityModerationController::class, 'index'])->name('content.community-posts.pending');
    Route::post('content/community-posts/{communityPost}/approve', [AdminCommunityModerationController::class, 'approve'])->name('content.community-posts.approve');
    Route::post('content/community-posts/{communityPost}/reject', [AdminCommunityModerationController::class, 'reject'])->name('content.community-posts.reject');
    Route::get('content/community-reports', [AdminCommunityReportController::class, 'index'])->name('content.community-reports.index');
    Route::post('content/community-reports/{communityPostReport}/dismiss', [AdminCommunityReportController::class, 'dismiss'])->name('content.community-reports.dismiss');
    Route::post('content/community-reports/{communityPostReport}/resolve', [AdminCommunityReportController::class, 'resolve'])->name('content.community-reports.resolve');
    Route::resource('content/community-categories', AdminCommunityCategoryController::class)->names('content.community-categories')->except(['show']);
    Route::resource('content/post-categories', AdminContentPostCategoryController::class)->names('content.post-categories')->except(['show']);
    Route::resource('content/series', AdminContentSeriesController::class)->names('content.series')->except(['show']);
    Route::resource('content/side-hustle-categories', AdminSideHustleCategoryController::class)->names('content.side-hustle-categories')->except(['show']);
    Route::resource('content/podcast-shows', AdminPodcastShowController::class)->names('content.podcast-shows')->except(['show']);
    Route::resource('content/podcast-episodes', AdminPodcastEpisodeController::class)->names('content.podcast-episodes')->except(['show']);
});
