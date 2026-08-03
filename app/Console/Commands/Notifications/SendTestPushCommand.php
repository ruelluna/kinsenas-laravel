<?php

namespace App\Console\Commands\Notifications;

use App\Models\User;
use App\Notifications\System\TestPushNotification;
use App\Services\Notifications\PushNotificationDiagnostics;
use Illuminate\Console\Command;

class SendTestPushCommand extends Command
{
    protected $signature = 'notifications:send-test-push
                            {email? : Target user email (defaults to the running admin)}
                            {--all : Send to all push subscribers}
                            {--title= : Notification title}
                            {--body= : Notification body}
                            {--url=/launch : Action URL when the notification is clicked}';

    protected $description = 'Send a test Web Push notification (platform admins only)';

    public function handle(PushNotificationDiagnostics $diagnostics): int
    {
        $admin = auth()->user();

        if (! $admin instanceof User || ! $admin->isPlatformAdmin()) {
            $this->error('Only platform admins can send test push notifications.');

            return self::FAILURE;
        }

        $server = $diagnostics->serverStatus();

        $this->info('Server push checklist:');
        $this->line($server['vapidConfigured'] ? '  [ok] VAPID keys configured' : '  [!!] VAPID keys missing in .env');

        $title = (string) ($this->option('title') ?: 'Kinsenas test push');
        $body = (string) ($this->option('body') ?: 'If you see this, Web Push is working.');
        $actionUrl = (string) ($this->option('url') ?: '/launch');

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
            $this->line('Inbox entry = job ran. OS popup = service worker + encoding + browser permission.');

            return self::SUCCESS;
        }

        $email = $this->argument('email') ?: $admin->email;

        $target = User::query()->where('email', $email)->first();

        if ($target === null) {
            $this->error("No user found with email [{$email}].");

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("Target checklist for {$target->email}:");
        foreach ($diagnostics->checklistForUser($target) as $item) {
            $this->line("  - {$item}");
        }
        $this->newLine();

        if ($target->pushSubscriptions()->doesntExist()) {
            $this->warn('No push subscription on file — inbox notification will still be queued.');
        }

        $target->notify($notification);

        $this->info("Test push queued for {$target->email}.");
        $this->line('Check bell inbox on that account. OS notification confirms Web Push delivery.');

        return self::SUCCESS;
    }
}
