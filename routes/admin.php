<?php

use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\Admin\AdminBetaFeedbackController;
use App\Http\Controllers\Admin\AdminContentPostController;
use App\Http\Controllers\Admin\AdminContentSeriesController;
use App\Http\Controllers\Admin\AdminContentStatsController;
use App\Http\Controllers\Admin\AdminNotificationTestController;
use App\Http\Controllers\Admin\AdminPaymentQrController;
use App\Http\Controllers\Admin\AdminPaymentSubmissionController;
use App\Http\Controllers\Admin\AdminPlatformUserController;
use App\Http\Controllers\Admin\AdminSavingsFormulaTemplateController;
use App\Http\Controllers\Admin\AdminSavingsPlanPageGuidanceController;
use App\Http\Controllers\Admin\AdminSubscriberController;
use App\Http\Controllers\Admin\AdminSubscriptionPlanController;
use App\Http\Controllers\Learn\LearnPostController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('subscribers', [AdminSubscriberController::class, 'index'])->name('subscribers.index');
        Route::get('notifications-test', [AdminNotificationTestController::class, 'index'])->name('notifications-test.index');
        Route::post('notifications-test', [AdminNotificationTestController::class, 'store'])->name('notifications-test.store');
        Route::get('subscribers/{team}', [AdminSubscriberController::class, 'show'])->name('subscribers.show');
        Route::post('subscribers/{team}/extend-trial', [AdminSubscriberController::class, 'extendTrial'])->name('subscribers.extend-trial');
        Route::post('subscribers/{team}/cancel', [AdminSubscriberController::class, 'cancel'])->name('subscribers.cancel');
        Route::post('subscribers/{team}/activate', [AdminSubscriberController::class, 'activate'])->name('subscribers.activate');
        Route::post('subscribers/{team}/change-plan', [AdminSubscriberController::class, 'changePlan'])->name('subscribers.change-plan');

        Route::get('plans', [AdminSubscriptionPlanController::class, 'index'])->name('plans.index');
        Route::get('plans/create', [AdminSubscriptionPlanController::class, 'create'])->name('plans.create');
        Route::post('plans', [AdminSubscriptionPlanController::class, 'store'])->name('plans.store');
        Route::get('plans/{plan}/edit', [AdminSubscriptionPlanController::class, 'edit'])->name('plans.edit');
        Route::put('plans/{plan}', [AdminSubscriptionPlanController::class, 'update'])->name('plans.update');

        Route::get('beta-feedback', [AdminBetaFeedbackController::class, 'index'])->name('beta-feedback.index');

        Route::get('payment-qr', [AdminPaymentQrController::class, 'edit'])->name('payment-qr.edit');
        Route::post('payment-qr', [AdminPaymentQrController::class, 'update'])->name('payment-qr.update');
        Route::get('payment-submissions', [AdminPaymentSubmissionController::class, 'index'])->name('payment-submissions.index');
        Route::post('payment-submissions/{submission}/approve', [AdminPaymentSubmissionController::class, 'approve'])->name('payment-submissions.approve');
        Route::post('payment-submissions/{submission}/reject', [AdminPaymentSubmissionController::class, 'reject'])->name('payment-submissions.reject');

        Route::get('platform-users', [AdminPlatformUserController::class, 'index'])->name('platform-users.index');
        Route::patch('platform-users/{user}', [AdminPlatformUserController::class, 'update'])->name('platform-users.update');
        Route::delete('platform-users/{user}', [AdminPlatformUserController::class, 'destroy'])->name('platform-users.destroy');

        Route::get('activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('savings-plan-guidance', [AdminSavingsPlanPageGuidanceController::class, 'edit'])->name('savings-plan-guidance.edit');
        Route::put('savings-plan-guidance', [AdminSavingsPlanPageGuidanceController::class, 'update'])->name('savings-plan-guidance.update');
        Route::get('formula-templates', [AdminSavingsFormulaTemplateController::class, 'index'])->name('formula-templates.index');
        Route::get('formula-templates/{template}/edit', [AdminSavingsFormulaTemplateController::class, 'edit'])->name('formula-templates.edit');
        Route::put('formula-templates/{template}', [AdminSavingsFormulaTemplateController::class, 'update'])->name('formula-templates.update');

        Route::get('content/stats', AdminContentStatsController::class)->name('content.stats');
        Route::get('content/posts/{post}/preview', [LearnPostController::class, 'preview'])->name('content.posts.preview');
        Route::resource('content/series', AdminContentSeriesController::class)->names('content.series')->except(['show']);
        Route::resource('content/posts', AdminContentPostController::class)->names('content.posts')->except(['show']);
    });
