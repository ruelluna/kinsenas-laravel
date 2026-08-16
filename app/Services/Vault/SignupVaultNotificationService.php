<?php

namespace App\Services\Vault;

use App\Models\User;
use App\Notifications\Admin\NewUserRegistered;
use App\Notifications\Vault\RecoveryKeyIssued;
use Illuminate\Support\Facades\Notification;

class SignupVaultNotificationService
{
    public function dispatch(User $user, string $recoveryKey): void
    {
        $user->notify(new RecoveryKeyIssued($recoveryKey));

        Notification::route('mail', config('signup.admin_notify.to'))
            ->notify(new NewUserRegistered($user));
    }
}
