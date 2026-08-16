<?php

use App\Http\Controllers\Admin\AdminContentPostController;
use App\Http\Controllers\Admin\AdminContentSeriesController;
use App\Http\Controllers\Admin\AdminContentStatsController;
use App\Http\Controllers\Admin\AdminContentUploadController;
use App\Http\Controllers\Learn\LearnPostController;
use Illuminate\Support\Facades\Route;

Route::get('content/stats', AdminContentStatsController::class)->name('content.stats');
Route::post('content/uploads', [AdminContentUploadController::class, 'store'])->name('content.uploads.store');
Route::get('content/posts/{post}/preview', [LearnPostController::class, 'preview'])->name('content.posts.preview');
Route::resource('content/series', AdminContentSeriesController::class)->names('content.series')->except(['show']);
Route::resource('content/posts', AdminContentPostController::class)->names('content.posts')->except(['show']);
