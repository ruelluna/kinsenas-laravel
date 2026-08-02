<?php

namespace App\Console\Commands;

use App\Enums\BillingMode;
use App\Services\Marketing\GhlMarketingService;
use Illuminate\Console\Command;

class GhlDiagnoseCommand extends Command
{
    protected $signature = 'ghl:diagnose {--upsert= : Optional email to upsert as a connectivity smoke test}';

    protected $description = 'Report GoHighLevel config, queue connection, and optional upsert test';

    public function handle(GhlMarketingService $ghlMarketingService): int
    {
        $this->info('GoHighLevel diagnostics');
        $this->newLine();

        $this->table(
            ['Setting', 'Value'],
            [
                ['GHL enabled', config('services.ghl.enabled') ? 'yes' : 'no'],
                ['Disabled reason', $ghlMarketingService->disabledReason() ?? '(none — ready)'],
                ['PIT set', $this->maskSecret(config('services.ghl.pit'))],
                ['Location ID', $this->maskSecret(config('services.ghl.location_id'))],
                ['Base URL', config('services.ghl.base_url')],
                ['API version', config('services.ghl.api_version')],
                ['Billing mode', BillingMode::current()->value],
                ['Queue connection', config('queue.default')],
            ],
        );

        $upsertEmail = $this->option('upsert');

        if (! is_string($upsertEmail) || trim($upsertEmail) === '') {
            if (! $ghlMarketingService->isEnabled()) {
                $this->warn('GHL sync is disabled. Set GHL_ENABLED=true, GHL_PIT, and GHL_LOCATION_ID, then run queue:work when using the database queue.');
            } elseif (config('queue.default') !== 'sync') {
                $this->comment('Reminder: database/redis queues require a running queue worker.');
            }

            return self::SUCCESS;
        }

        if (! $ghlMarketingService->isEnabled()) {
            $this->error('Cannot upsert: '.$ghlMarketingService->disabledReason());

            return self::FAILURE;
        }

        $contactId = $ghlMarketingService->ensureContact(
            trim($upsertEmail),
            'GHL Diagnose',
            ['event' => 'ghl_diagnose'],
        );

        if ($contactId === null) {
            $this->error('Upsert failed — check storage/logs/laravel.log for GHL warnings.');

            return self::FAILURE;
        }

        $this->info("Upsert OK — contact ID: {$contactId}");

        return self::SUCCESS;
    }

    private function maskSecret(mixed $value): string
    {
        if (! is_string($value) || $value === '') {
            return '(not set)';
        }

        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4).'…'.substr($value, -4);
    }
}
