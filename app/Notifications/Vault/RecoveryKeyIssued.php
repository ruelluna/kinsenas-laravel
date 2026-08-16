<?php

namespace App\Notifications\Vault;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecoveryKeyIssued extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $recoveryKey)
    {
        $this->afterCommit();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Save your :app recovery key', ['app' => config('app.name')]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your private vault is ready. Below is your recovery key — the backup secret that unlocks your encrypted data if you forget your password or your vault is locked.'))
            ->line(__('**This is the only time we will email this key.** Store it somewhere safe, such as a password manager or secure notes app.'))
            ->line(__('**Do not delete this email.** You cannot retrieve this recovery key again from :app.', ['app' => config('app.name')]))
            ->line($this->recoveryKey)
            ->action(__('Log in to Kinsenas'), route('login'));
    }
}
