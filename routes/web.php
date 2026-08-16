<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Learn\LearnIndexController;
use App\Http\Controllers\Learn\LearnPostController;
use App\Http\Controllers\Learn\LearnPostReactionController;
use App\Http\Controllers\Learn\LearnSeriesController;
use App\Http\Controllers\Marketing\SurveyResponseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PwaLaunchController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\Vault\VaultUnlockController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');
Route::get('launch', PwaLaunchController::class)->name('pwa.launch');
Route::inertia('/survey', 'marketing/survey')->name('survey');
Route::post('survey/responses', [SurveyResponseController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('survey.responses.store');

Route::get('learn', LearnIndexController::class)->name('learn.index');
Route::get('learn/series/{series}', [LearnSeriesController::class, 'show'])->name('learn.series.show');
Route::get('learn/posts/{post}', [LearnPostController::class, 'show'])->name('learn.posts.show');

Route::middleware(['auth', 'verified', 'learn.member'])->group(function () {
    Route::post('learn/posts/{post}/react', [LearnPostReactionController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('learn.posts.react');
});

Route::middleware(['auth'])->get('dashboard', function () {
    return redirect()->route('pwa.launch');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

Route::middleware(['auth', 'verified', 'subscribed'])->group(function () {
    Route::get('vault/unlock', [VaultUnlockController::class, 'create'])->name('vault.unlock');
    Route::post('vault/unlock', [VaultUnlockController::class, 'store'])->name('vault.unlock.store');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class, 'subscribed', 'vault.unlocked'])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        require __DIR__.'/savings.php';
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('permission:admin.manage-platform')
        ->group(function () {
            require __DIR__.'/admin-ops.php';
        });

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('permission:admin.manage-content')
        ->group(function () {
            require __DIR__.'/admin-content.php';
        });
});

require __DIR__.'/settings.php';
