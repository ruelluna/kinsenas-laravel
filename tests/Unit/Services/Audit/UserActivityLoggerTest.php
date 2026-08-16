<?php

use App\Enums\UserActivityAction;
use App\Models\Team;
use App\Models\User;
use App\Services\Audit\UserActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

it('persists sanitized activity rows', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $activity = app(UserActivityLogger::class)->log(
        UserActivityAction::TeamInvitationSent,
        'Sent team invitation to :properties.email',
        $user,
        properties: [
            'email' => 'invited@example.com',
            'role_label' => 'Member',
        ],
        team: $team,
    );

    expect($activity)->toBeInstanceOf(Activity::class);

    $this->assertDatabaseHas('activity_log', [
        'id' => $activity->id,
        'event' => UserActivityAction::TeamInvitationSent->value,
        'log_name' => 'kinsenas',
    ]);

    $stored = Activity::query()->findOrFail($activity->id);

    expect($stored->causer?->is($user))->toBeTrue()
        ->and(data_get($stored->properties, 'email'))->toBe('invited@example.com')
        ->and(data_get($stored->properties, 'team_id'))->toBe($team->id)
        ->and(data_get($stored->properties, 'amount'))->toBeNull();
});
