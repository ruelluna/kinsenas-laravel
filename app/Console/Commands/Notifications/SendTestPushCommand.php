<?php

namespace App\Console\Commands\Notifications;

use App\Models\User;
use App\Notifications\System\TestPushNotification;
use Illuminate\Console\Command;

class SendTestPushCommand extends Command
{
    protected $signature = 'notifications:send-test-push
                            {email? : Target user email (defaults to the running admin)}
                            {--all : Send to all push subscribers}
                            {--title= : Notification title}
                            {--body= : Notification body}
                            {--url=/dashboard : Action URL when the notification is clicked}';

    protected $description = 'Send a test Web Push notification (platform admins only)';

    public function handle(): int
    {
        $admin = auth()->user();

        if (! $admin instanceof User || ! $admin->isPlatformAdmin()) {
            $this->error('Only platform admins can send test push notifications.');

            return self::FAILURE;
        }

        $title = (string) ($this->option('title') ?: 'Kinsenas test push');
        $body = (string) ($this->option('body') ?: 'If you see this, Web Push is working.');
        $actionUrl = (string) ($this->option('url') ?: '/dashboard');

        $notification = new TestPushNotification($title, $body, $actionUrl);

        if ($this->option('all')) {
            if (! $this->confirm('Send test push to all users with an active push subscription?')) {
                $this->info('Cancelled.');

                return self::SUCCESS;
            }

            $sent = 0;

            User::query()
                ->whereHas('pushSubscriptions')
                ->whereHas('notificationPreferences', fn ($query) => $query->where('push_enabled', true))
                ->each(function (User $user) use ($notification, &$sent): void {
                    $user->notify($notification);
                    $sent++;
                });

            $this->info("Sent {$sent} test push notification(s).");

            return self::SUCCESS;
        }

        $email = $this->argument('email') ?: $admin->email;

        $target = User::query()->where('email', $email)->first();

        if ($target === null) {
            $this->error("No user found with email [{$email}].");

            return self::FAILURE;
        }

        if ($target->pushSubscriptions()->doesntExist()) {
            $this->warn("User [{$email}] has no push subscription. Notification will still be stored in the inbox.");
        }

        $target->notify($notification);

        $this->info("Test push sent to {$target->email}.");

        return self::SUCCESS;
    }
}
