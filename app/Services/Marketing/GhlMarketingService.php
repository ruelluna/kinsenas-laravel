<?php

namespace App\Services\Marketing;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhlMarketingService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.ghl.enabled', false);
    }

    public function syncApplicationEvent(User $user, string $event): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $webhookUrl = match ($event) {
            'application_submitted' => config('services.ghl.webhook_application_url'),
            'application_approved' => config('services.ghl.webhook_approved_url'),
            'application_rejected' => config('services.ghl.webhook_rejected_url'),
            default => null,
        };

        if (! is_string($webhookUrl) || $webhookUrl === '') {
            return;
        }

        $payload = [
            'event' => $event,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'beta_application_status' => $user->beta_application_status?->value,
                'beta_applied_at' => $user->beta_enrolled_at?->toIso8601String(),
                'beta_approved_at' => $user->beta_approved_at?->toIso8601String(),
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            ],
        ];

        try {
            $response = Http::timeout(10)->post($webhookUrl, $payload);

            if (! $response->successful()) {
                Log::warning('GHL webhook returned non-success status', [
                    'event' => $event,
                    'user_id' => $user->id,
                    'status' => $response->status(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::error('GHL webhook request failed', [
                'event' => $event,
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
