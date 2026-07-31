<?php

use App\Http\Controllers\Admin\AdminPaymentSubmissionController;
use App\Http\Controllers\Admin\AdminPaymentQrController;
use App\Http\Controllers\Admin\AdminSubscriptionPlanController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('plans', [AdminSubscriptionPlanController::class, 'index'])->name('plans.index');
        Route::get('payment-qr', [AdminPaymentQrController::class, 'edit'])->name('payment-qr.edit');
        Route::post('payment-qr', [AdminPaymentQrController::class, 'update'])->name('payment-qr.update');
        Route::get('payment-submissions', [AdminPaymentSubmissionController::class, 'index'])->name('payment-submissions.index');
        Route::post('payment-submissions/{submission}/approve', [AdminPaymentSubmissionController::class, 'approve'])->name('payment-submissions.approve');
        Route::post('payment-submissions/{submission}/reject', [AdminPaymentSubmissionController::class, 'reject'])->name('payment-submissions.reject');
    });
