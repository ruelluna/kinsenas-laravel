<?php

use App\Models\SurveyResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function validSurveyPayload(array $overrides = []): array
{
    return array_merge([
        'language' => 'en',
        'email' => 'survey@example.com',
        'name' => 'Maria Santos',
        'result' => 'family-first-planner',
        'completed_at' => now()->toIso8601String(),
        'answers' => [
            'q1' => 'employee',
            'q2' => 'single',
            'q3' => '2-3',
            'q4' => 'send_family',
            'q5' => ['family_support', 'bills'],
            'q6' => 'manual',
            'q7' => 'forgetting_transfers',
            'q9' => 'track_transfers',
            'q10' => 'early_access',
        ],
    ], $overrides);
}

it('stores a public survey response', function () {
    $response = $this->postJson(route('survey.responses.store'), validSurveyPayload());

    $response->assertCreated();
    $response->assertJsonStructure(['id']);

    $this->assertDatabaseHas('survey_responses', [
        'email' => 'survey@example.com',
        'language' => 'en',
        'result' => 'family-first-planner',
    ]);

    $stored = SurveyResponse::query()->first();

    expect($stored)->not->toBeNull()
        ->and($stored->answers['q5'])->toBe(['family_support', 'bills']);
});

it('accepts skipped optional spending habit answers', function () {
    $response = $this->postJson(route('survey.responses.store'), validSurveyPayload([
        'answers' => array_merge(validSurveyPayload()['answers'], [
            'q8' => [],
        ]),
    ]));

    $response->assertCreated();

    $this->assertDatabaseHas('survey_responses', [
        'email' => 'survey@example.com',
    ]);
});

it('validates required survey fields', function () {
    $response = $this->postJson(route('survey.responses.store'), []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['language', 'email', 'result', 'completed_at', 'answers']);
});

it('rejects invalid answer values', function () {
    $payload = validSurveyPayload([
        'answers' => array_merge(validSurveyPayload()['answers'], [
            'q1' => 'not-a-valid-role',
        ]),
    ]);

    $response = $this->postJson(route('survey.responses.store'), $payload);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['answers.q1']);
});

it('accepts completedAt from the frontend payload', function () {
    $payload = validSurveyPayload();
    unset($payload['completed_at']);
    $payload['completedAt'] = now()->toIso8601String();

    $response = $this->postJson(route('survey.responses.store'), $payload);

    $response->assertCreated();
});
