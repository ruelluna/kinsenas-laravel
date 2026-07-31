<?php

use App\Http\Controllers\Savings\BankController;
use App\Http\Controllers\Savings\FundSpendController;
use App\Http\Controllers\Savings\FundTransferController;
use App\Http\Controllers\Savings\IncomePeriodController;
use App\Http\Controllers\Savings\RecipientController;
use App\Http\Controllers\Savings\SavingsPlanController;
use App\Http\Controllers\Savings\SavingsReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('savings')
    ->name('savings.')
    ->group(function () {
        Route::get('plan', [SavingsPlanController::class, 'show'])->name('plan.show');
        Route::post('plan/from-template/{template}', [SavingsPlanController::class, 'storeFromTemplate'])->name('plan.from-template');
        Route::put('plan', [SavingsPlanController::class, 'update'])->name('plan.update');

        Route::get('income', [IncomePeriodController::class, 'index'])->name('income.index');
        Route::get('income/{incomePeriod}', [IncomePeriodController::class, 'show'])->name('income.show');
        Route::post('income', [IncomePeriodController::class, 'store'])->name('income.store');
        Route::put('income/{incomePeriod}/custom-amounts', [IncomePeriodController::class, 'updateCustomAmounts'])->name('income.custom-amounts');
        Route::post('income/{incomePeriod}/lock', [IncomePeriodController::class, 'lock'])->name('income.lock');
        Route::post('income/{incomePeriod}/unlock', [IncomePeriodController::class, 'unlock'])->name('income.unlock');

        Route::get('banks', [BankController::class, 'index'])->name('banks.index');
        Route::post('banks', [BankController::class, 'store'])->name('banks.store');
        Route::put('banks/{bank}', [BankController::class, 'update'])->name('banks.update');
        Route::delete('banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');

        Route::get('recipients', [RecipientController::class, 'index'])->name('recipients.index');
        Route::post('recipients', [RecipientController::class, 'store'])->name('recipients.store');
        Route::put('recipients/{recipient}', [RecipientController::class, 'update'])->name('recipients.update');
        Route::delete('recipients/{recipient}', [RecipientController::class, 'destroy'])->name('recipients.destroy');

        Route::get('spending', [FundSpendController::class, 'index'])->name('spending.index');
        Route::post('spending', [FundSpendController::class, 'store'])->name('spending.store');
        Route::post('spending/{fundSpend}/confirm', [FundSpendController::class, 'confirm'])->name('spending.confirm');

        Route::get('transfers', [FundTransferController::class, 'index'])->name('transfers.index');
        Route::post('transfers', [FundTransferController::class, 'store'])->name('transfers.store');
        Route::post('transfers/{fundTransfer}/confirm', [FundTransferController::class, 'confirm'])->name('transfers.confirm');

        Route::get('reports', SavingsReportController::class)->name('reports');
    });
