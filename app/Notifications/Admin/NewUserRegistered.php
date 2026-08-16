<?php

namespace App\Notifications\Admin;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user)
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
        $message = (new MailMessage)
            ->subject(__('New :app signup: :name', [
                'app' => config('app.name'),
                'name' => $this->user->name,
            ]))
            ->line(__('A new member signed up for :app.', ['app' => config('app.name')]))
            ->line(__('Name: :name', ['name' => $this->user->name]))
            ->line(__('Email: :email', ['email' => $this->user->email]))
            ->line(__('Signed up: :time', ['time' => $this->user->created_at?->toDayDateTimeString() ?? __('Unknown')]))
            ->line(__('The recovery key was sent directly to the user; it is not included in this email.'));

        $cc = config('signup.admin_notify.cc');

        if (is_string($cc) && $cc !== '') {
            $message->cc($cc);
        }

        return $message;
    }
}
