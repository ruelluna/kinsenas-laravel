<?php

namespace App\Services\Marketing;

use App\Models\SurveyResponse;
use App\Models\User;
use App\Support\Survey\SurveyGhlTagBuilder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhlMarketingService
{
    public function isEnabled(): bool
    {
        return $this->disabledReason() === null;
    }

    public function disabledReason(): ?string
    {
        if (! (bool) config('services.ghl.enabled', false)) {
            return 'enabled=false';
        }

        $pit = config('services.ghl.pit');

        if (! is_string($pit) || $pit === '') {
            return 'missing_pit';
        }

        $locationId = config('services.ghl.location_id');

        if (! is_string($locationId) || $locationId === '') {
            return 'missing_location_id';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function guardEnabled(array $context): bool
    {
        $reason = $this->disabledReason();

        if ($reason === null) {
            return true;
        }

        Log::info('GHL sync skipped', [
            ...$context,
            'reason' => $reason,
        ]);

        return false;
    }

    public function syncSurveyResponse(SurveyResponse $surveyResponse): void
    {
        if (! $this->guardEnabled([
            'event' => 'survey_completed',
            'survey_response_id' => $surveyResponse->id,
        ])) {
            return;
        }

        $this->mutateTags(
            email: $surveyResponse->email,
            name: $surveyResponse->name,
            tagsToAdd: SurveyGhlTagBuilder::from($surveyResponse),
            tagsToRemove: [],
            context: [
                'event' => 'survey_completed',
                'survey_response_id' => $surveyResponse->id,
            ],
        );
    }

    public function syncUserTags(User $user, array $tagsToAdd = [], array $tagsToRemove = [], array $context = []): void
    {
        if (! $this->guardEnabled(array_merge($context, ['user_id' => $user->id]))) {
            return;
        }

        $this->mutateTags(
            email: $user->email,
            name: $user->name,
            tagsToAdd: $tagsToAdd,
            tagsToRemove: $tagsToRemove,
            context: array_merge($context, ['user_id' => $user->id]),
        );
    }

    /**
     * @param  list<string>  $tagsToAdd
     * @param  list<string>  $tagsToRemove
     * @param  array<string, mixed>  $context
     */
    public function mutateTags(
        string $email,
        ?string $name,
        array $tagsToAdd = [],
        array $tagsToRemove = [],
        array $context = [],
    ): void {
        if (! $this->guardEnabled($context)) {
            return;
        }

        $tagsToAdd = array_values(array_unique(array_filter($tagsToAdd)));
        $tagsToRemove = array_values(array_unique(array_filter($tagsToRemove)));

        if ($tagsToAdd === [] && $tagsToRemove === []) {
            return;
        }

        $contactId = $this->ensureContact($email, $name, $context);

        if ($contactId === null) {
            return;
        }

        if ($tagsToAdd !== []) {
            $this->addTags($contactId, $tagsToAdd, $context);
        }

        if ($tagsToRemove !== []) {
            $this->removeTags($contactId, $tagsToRemove, $context);
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function ensureContact(string $email, ?string $name, array $context = []): ?string
    {
        $baseUrl = rtrim((string) config('services.ghl.base_url'), '/');
        $payload = [
            'locationId' => config('services.ghl.location_id'),
            'email' => $email,
        ];

        $nameParts = $this->splitName($name);

        if ($nameParts['firstName'] !== null) {
            $payload['firstName'] = $nameParts['firstName'];
        }

        if ($nameParts['lastName'] !== null) {
            $payload['lastName'] = $nameParts['lastName'];
        }

        if (is_string($name) && trim($name) !== '') {
            $payload['name'] = trim($name);
        }

        try {
            $response = Http::baseUrl($baseUrl)
                ->timeout(10)
                ->withToken((string) config('services.ghl.pit'))
                ->withHeaders([
                    'Version' => (string) config('services.ghl.api_version'),
                    'Accept' => 'application/json',
                ])
                ->post('/contacts/upsert', $payload);

            if (! $response->successful()) {
                Log::warning('GHL contact upsert returned non-success status', [
                    ...$context,
                    'email' => $email,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $contactId = $this->extractContactId($response->json());

            if ($contactId === null) {
                Log::warning('GHL contact upsert succeeded but contact id missing', [
                    ...$context,
                    'email' => $email,
                    'body' => $response->body(),
                ]);
            }

            return $contactId;
        } catch (\Throwable $exception) {
            Log::error('GHL contact upsert request failed', [
                ...$context,
                'email' => $email,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * @param  list<string>  $tags
     * @param  array<string, mixed>  $context
     */
    public function addTags(string $contactId, array $tags, array $context = []): void
    {
        $this->mutateContactTags('post', $contactId, $tags, $context, 'add');
    }

    /**
     * @param  list<string>  $tags
     * @param  array<string, mixed>  $context
     */
    public function removeTags(string $contactId, array $tags, array $context = []): void
    {
        $this->mutateContactTags('delete', $contactId, $tags, $context, 'remove');
    }

    /**
     * @param  list<string>  $tags
     * @param  array<string, mixed>  $context
     */
    private function mutateContactTags(string $method, string $contactId, array $tags, array $context, string $action): void
    {
        if ($tags === []) {
            return;
        }

        $baseUrl = rtrim((string) config('services.ghl.base_url'), '/');

        try {
            $response = Http::baseUrl($baseUrl)
                ->timeout(10)
                ->withToken((string) config('services.ghl.pit'))
                ->withHeaders([
                    'Version' => (string) config('services.ghl.api_version'),
                    'Accept' => 'application/json',
                ])
                ->{$method}("/contacts/{$contactId}/tags", [
                    'tags' => array_values(array_unique($tags)),
                ]);

            if (! $response->successful()) {
                Log::warning("GHL contact tag {$action} returned non-success status", [
                    ...$context,
                    'contact_id' => $contactId,
                    'tags' => $tags,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::error("GHL contact tag {$action} request failed", [
                ...$context,
                'contact_id' => $contactId,
                'tags' => $tags,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function extractContactId(?array $payload): ?string
    {
        if ($payload === null) {
            return null;
        }

        $contactId = data_get($payload, 'contact.id') ?? data_get($payload, 'id');

        return is_string($contactId) && $contactId !== '' ? $contactId : null;
    }

    /**
     * @return array{firstName: ?string, lastName: ?string}
     */
    private function splitName(?string $name): array
    {
        if (! is_string($name)) {
            return ['firstName' => null, 'lastName' => null];
        }

        $trimmed = trim($name);

        if ($trimmed === '') {
            return ['firstName' => null, 'lastName' => null];
        }

        $parts = preg_split('/\s+/', $trimmed, 2);

        return [
            'firstName' => $parts[0] ?? null,
            'lastName' => $parts[1] ?? null,
        ];
    }
}
