<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\Vault\VaultUnlockController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('beta/pending', 'auth/beta-pending')->name('beta.pending');
    Route::inertia('beta/rejected', 'auth/beta-rejected')->name('beta.rejected');
});

Route::middleware(['auth', 'verified', 'beta.approved', 'subscribed'])->group(function () {
    Route::get('vault/unlock', [VaultUnlockController::class, 'create'])->name('vault.unlock');
    Route::post('vault/unlock', [VaultUnlockController::class, 'store'])->name('vault.unlock.store');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', 'beta.approved', EnsureTeamMembership::class, 'vault.unlocked', 'subscribed'])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        require __DIR__.'/savings.php';
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

Route::middleware(['auth', 'verified', 'platform.admin'])
    ->group(function () {
        require __DIR__.'/admin.php';
    });

require __DIR__.'/settings.php';
