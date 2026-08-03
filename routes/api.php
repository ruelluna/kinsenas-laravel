<?php

use App\Http\Controllers\Api\V1\Admin\BetaApplicationController;
use App\Http\Controllers\Api\V1\Admin\PlanController;
use App\Http\Controllers\Api\V1\Admin\PlatformUserController;
use App\Http\Controllers\Api\V1\Admin\SubscriberController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\Savings\IncomeController;
use App\Http\Controllers\Api\V1\Savings\SpendingController;
use App\Http\Controllers\Api\V1\Savings\TransferController;
use App\Http\Controllers\Api\V1\TeamSwitchController;
use App\Http\Controllers\Api\V1\VaultUnlockController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/login', LoginController::class)->name('auth.login');

    Route::middleware(['auth:sanctum', 'verified', 'beta.approved'])->group(function () {
        Route::get('auth/me', MeController::class)->name('auth.me');
        Route::post('auth/logout', LogoutController::class)->name('auth.logout');
        Route::post('vault/unlock', VaultUnlockController::class)->name('vault.unlock');
        Route::post('teams/switch', TeamSwitchController::class)->name('teams.switch');

        Route::middleware('subscribed')->group(function () {
            Route::prefix('teams/{team:id}')
                ->middleware('api.team')
                ->group(function () {
                    Route::middleware('vault.unlocked')->group(function () {
                        Route::get('dashboard', DashboardController::class)->name('dashboard');
                        Route::get('billing', [BillingController::class, 'show'])->name('billing');

                        Route::prefix('savings')->name('savings.')->group(function () {
                            Route::middleware('subscribed.feature:savings_plan')->group(function () {
                                Route::get('income', [IncomeController::class, 'index'])->name('income.index');
                                Route::get('spending', [SpendingController::class, 'index'])->name('spending.index');
                            });

                            Route::middleware(['subscribed.feature:transfers', 'savings.plan.required'])->group(function () {
                                Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');
                            });
                        });
                    });
                });
        });

        Route::prefix('admin')
            ->middleware('platform.admin')
            ->name('admin.')
            ->group(function () {
                Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
                Route::get('subscribers/{team}', [SubscriberController::class, 'show'])->name('subscribers.show');
                Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
                Route::get('beta-applications', [BetaApplicationController::class, 'index'])->name('beta-applications.index');
                Route::get('platform-users', [PlatformUserController::class, 'index'])->name('platform-users.index');
            });
    });
});
