<?php

use App\Http\Controllers\Api\V1\Admin\PlanController;
use App\Http\Controllers\Api\V1\Admin\PlatformUserController;
use App\Http\Controllers\Api\V1\Admin\SubscriberController;
use App\Http\Controllers\Api\V1\Auth\BootstrapController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RegisterContextController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\Notifications\NotificationController;
use App\Http\Controllers\Api\V1\Savings\BankController;
use App\Http\Controllers\Api\V1\Savings\IncomeController;
use App\Http\Controllers\Api\V1\Savings\PlanController as SavingsPlanController;
use App\Http\Controllers\Api\V1\Savings\RecipientController;
use App\Http\Controllers\Api\V1\Savings\ReportController;
use App\Http\Controllers\Api\V1\Savings\SpendingController;
use App\Http\Controllers\Api\V1\Savings\TransferController;
use App\Http\Controllers\Api\V1\Settings\BetaFeedbackController;
use App\Http\Controllers\Api\V1\Settings\BillingController as SettingsBillingController;
use App\Http\Controllers\Api\V1\Settings\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\Settings\PasswordController;
use App\Http\Controllers\Api\V1\Settings\PaymentSubmissionController;
use App\Http\Controllers\Api\V1\Settings\ProfileController;
use App\Http\Controllers\Api\V1\Teams\TeamController;
use App\Http\Controllers\Api\V1\Teams\TeamInvitationController;
use App\Http\Controllers\Api\V1\Teams\TeamMemberController;
use App\Http\Controllers\Api\V1\TeamSwitchController;
use App\Http\Controllers\Api\V1\VaultUnlockController;
use App\Http\Middleware\BindVaultKeyStore;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/login', LoginController::class)->name('auth.login');
    Route::post('auth/register', RegisterController::class)->name('auth.register');
    Route::get('auth/register-context', RegisterContextController::class)->name('auth.register-context');
    Route::post('auth/forgot-password', ForgotPasswordController::class)->name('auth.forgot-password');
    Route::post('auth/reset-password', ResetPasswordController::class)->name('auth.reset-password');

    Route::middleware(['auth:sanctum', BindVaultKeyStore::class])->group(function () {
        Route::get('auth/bootstrap', BootstrapController::class)->name('auth.bootstrap');
        Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
        Route::post('invitations/{invitation}/decline', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
    });

    Route::middleware(['auth:sanctum', BindVaultKeyStore::class, 'verified'])->group(function () {
        Route::get('auth/me', MeController::class)->name('auth.me');
        Route::post('auth/logout', LogoutController::class)->name('auth.logout');
        Route::post('vault/unlock', VaultUnlockController::class)->name('vault.unlock');
        Route::post('teams/switch', TeamSwitchController::class)->name('teams.switch');

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
            Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
            Route::put('password', [PasswordController::class, 'update'])->middleware('throttle:6,1')->name('password.update');

            Route::get('notifications', [NotificationPreferenceController::class, 'show'])->name('notifications.show');
            Route::patch('notifications', [NotificationPreferenceController::class, 'update'])->name('notifications.update');

            Route::get('feedback', [BetaFeedbackController::class, 'create'])->name('feedback.create');
            Route::post('feedback', [BetaFeedbackController::class, 'store'])->name('feedback.store');

            Route::get('billing', [SettingsBillingController::class, 'show'])->name('billing.show');
            Route::post('billing/payments', [PaymentSubmissionController::class, 'store'])->name('billing.payments.store');
        });

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

        Route::get('teams', [TeamController::class, 'index'])->name('teams.index');
        Route::post('teams', [TeamController::class, 'store'])->name('teams.store');

        Route::prefix('teams/{team:id}')
            ->middleware('api.team')
            ->group(function () {
                Route::post('switch', [TeamController::class, 'switch'])->name('teams.member-switch');

                Route::middleware('subscribed')->group(function () {
                    Route::get('/', [TeamController::class, 'show'])->name('teams.show');
                    Route::patch('/', [TeamController::class, 'update'])->name('teams.update');
                    Route::delete('/', [TeamController::class, 'destroy'])->name('teams.destroy');
                    Route::delete('leave', [TeamController::class, 'leave'])->name('teams.leave');
                    Route::patch('members/{user}', [TeamMemberController::class, 'update'])->name('teams.members.update');
                    Route::delete('members/{user}', [TeamMemberController::class, 'destroy'])->name('teams.members.destroy');
                    Route::post('invitations', [TeamInvitationController::class, 'store'])->name('teams.invitations.store');
                    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'destroy'])->name('teams.invitations.destroy');
                });
            });

        Route::middleware('subscribed')->group(function () {
            Route::prefix('teams/{team:id}')
                ->middleware('api.team')
                ->group(function () {
                    Route::middleware('vault.unlocked')->group(function () {
                        Route::get('dashboard', DashboardController::class)->name('dashboard');
                        Route::get('billing', [BillingController::class, 'show'])->name('billing');

                        Route::prefix('savings')->name('savings.')->group(function () {
                            Route::middleware('subscribed.feature:savings_plan')->group(function () {
                                Route::get('plan', [SavingsPlanController::class, 'show'])->name('plan.show');
                                Route::post('plan/from-template/{template}', [SavingsPlanController::class, 'storeFromTemplate'])->name('plan.from-template');
                                Route::post('plan/custom', [SavingsPlanController::class, 'storeCustom'])->name('plan.custom');
                                Route::put('plan', [SavingsPlanController::class, 'update'])->name('plan.update');
                                Route::patch('plan/categories/{category}/opening-balance', [SavingsPlanController::class, 'addOpeningBalance'])->name('plan.category.opening-balance');
                                Route::delete('plan', [SavingsPlanController::class, 'destroy'])->name('plan.destroy');

                                Route::get('banks', [BankController::class, 'index'])->name('banks.index');
                                Route::post('banks', [BankController::class, 'store'])->name('banks.store');
                                Route::put('banks/{bank}', [BankController::class, 'update'])->name('banks.update');
                                Route::delete('banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');

                                Route::get('recipients', [RecipientController::class, 'index'])->name('recipients.index');
                                Route::post('recipients', [RecipientController::class, 'store'])->name('recipients.store');
                                Route::put('recipients/{recipient}', [RecipientController::class, 'update'])->name('recipients.update');
                                Route::delete('recipients/{recipient}', [RecipientController::class, 'destroy'])->name('recipients.destroy');

                                Route::middleware('savings.plan.required')->group(function () {
                                    Route::get('income', [IncomeController::class, 'index'])->name('income.index');
                                    Route::get('income/{incomePeriod}', [IncomeController::class, 'show'])->name('income.show');
                                    Route::post('income', [IncomeController::class, 'store'])->name('income.store');
                                    Route::put('income/{incomePeriod}/custom-amounts', [IncomeController::class, 'updateCustomAmounts'])->name('income.custom-amounts');
                                    Route::post('income/{incomePeriod}/todos/{todo}/complete', [IncomeController::class, 'completeDistributionTodo'])->name('income.todos.complete');
                                    Route::delete('income/{incomePeriod}', [IncomeController::class, 'destroy'])->name('income.destroy');

                                    Route::get('spending', [SpendingController::class, 'index'])->name('spending.index');
                                    Route::post('spending', [SpendingController::class, 'store'])->name('spending.store');
                                    Route::put('spending/{fundSpend}', [SpendingController::class, 'update'])->name('spending.update');
                                    Route::delete('spending/{fundSpend}', [SpendingController::class, 'destroy'])->name('spending.destroy');
                                    Route::post('spending/{fundSpend}/confirm', [SpendingController::class, 'confirm'])->name('spending.confirm');
                                    Route::post('spending/{fundSpend}/reimbursements', [SpendingController::class, 'storeReimbursement'])->name('spending.reimbursements.store');
                                    Route::post('spending/{fundSpend}/close-reimbursement', [SpendingController::class, 'closeReimbursement'])->name('spending.reimbursements.close');
                                });
                            });

                            Route::middleware(['subscribed.feature:transfers', 'savings.plan.required'])->group(function () {
                                Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');
                                Route::post('transfers', [TransferController::class, 'store'])->name('transfers.store');
                                Route::post('transfers/{fundTransfer}/confirm', [TransferController::class, 'confirm'])->name('transfers.confirm');
                            });

                            Route::middleware(['subscribed.feature:reports', 'savings.plan.required'])->group(function () {
                                Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
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
                Route::get('platform-users', [PlatformUserController::class, 'index'])->name('platform-users.index');
            });
    });
});
