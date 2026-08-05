<?php

use App\Models\User;
use App\Notifications\Teams\TeamInvitation as TeamInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createApiNotification(User $user): string
{
    return $user->notifications()->create([
        'id' => (string) Str::uuid7(),
        'type' => TeamInvitationNotification::class,
        'data' => [
            'kind' => 'team_invitation',
            'title' => 'Team invitation',
            'body' => 'You were invited.',
            'actionUrl' => '/dashboard',
            'meta' => [],
        ],
    ])->id;
}

it('lists the authenticated users notification inbox', function (): void {
    $user = User::factory()->betaApproved()->create();
    createApiNotification($user);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/notifications')
        ->assertSuccessful()
        ->assertJsonPath('unreadCount', 1)
        ->assertJsonPath('inbox.data.0.title', 'Team invitation');
});

it('marks only the authenticated users notification as read', function (): void {
    $user = User::factory()->betaApproved()->create();
    $notificationId = createApiNotification($user);
    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/notifications/{$notificationId}/read")
        ->assertSuccessful()
        ->assertJsonPath('unreadCount', 0);

    $this->assertDatabaseHas('notifications', [
        'id' => $notificationId,
        'notifiable_id' => $user->id,
    ]);

    expect($user->notifications()->find($notificationId)?->read_at)->not->toBeNull();
});

it('marks all notifications as read', function (): void {
    $user = User::factory()->betaApproved()->create();
    createApiNotification($user);
    createApiNotification($user);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/notifications/read-all')
        ->assertSuccessful()
        ->assertJsonPath('unreadCount', 0);

    expect($user->unreadNotifications()->count())->toBe(0);
});
