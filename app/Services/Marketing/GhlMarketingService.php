<?php

namespace App\Services\Marketing;

use App\Models\SurveyResponse;
use App\Models\User;
use App\Support\Marketing\GhlTagCatalog;
use App\Support\Survey\SurveyGhlTagBuilder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhlMarketingService
{
    public function isEnabled(): bool
    {
        if (! (bool) config('services.ghl.enabled', false)) {
            return false;
        }

        $pit = config('services.ghl.pit');
        $locationId = config('services.ghl.location_id');

        return is_string($pit) && $pit !== ''
            && is_string($locationId) && $locationId !== '';
    }

    public function syncApplicationEvent(User $user, string $event): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $tagMutation = GhlTagCatalog::betaEventTags($event);

        if ($tagMutation === null) {
            Log::warning('GHL beta sync skipped for unknown event', [
                'event' => $event,
                'user_id' => $user->id,
            ]);

            return;
        }

        [$tagsToAdd, $tagsToRemove] = $tagMutation;

        $this->mutateTags(
            email: $user->email,
            name: $user->name,
            tagsToAdd: $tagsToAdd,
            tagsToRemove: $tagsToRemove,
            context: [
                'event' => $event,
                'user_id' => $user->id,
            ],
        );
    }

    public function syncSurveyResponse(SurveyResponse $surveyResponse): void
    {
        if (! $this->isEnabled()) {
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
        if (! $this->isEnabled()) {
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
        if (! $this->isEnabled()) {
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

            return $this->extractContactId($response->json());
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
