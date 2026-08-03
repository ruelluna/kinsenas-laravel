<?php

use App\Models\User;
use App\Notifications\Teams\TeamInvitation as TeamInvitationNotification;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('shares unread notification count with inertia', function () {
    $user = User::factory()->create();

    $user->notifications()->create([
        'id' => (string) Str::uuid7(),
        'type' => TeamInvitationNotification::class,
        'data' => [
            'kind' => 'team_invitation',
            'title' => 'Team invitation',
            'body' => 'You were invited.',
            'actionUrl' => '/dashboard',
            'meta' => [],
        ],
    ]);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('notifications.unreadCount', 1));
});

it('lists notifications on the inbox page', function () {
    $user = User::factory()->create();

    $user->notifications()->create([
        'id' => (string) Str::uuid7(),
        'type' => TeamInvitationNotification::class,
        'data' => [
            'kind' => 'team_invitation',
            'title' => 'Team invitation',
            'body' => 'You were invited.',
            'actionUrl' => '/dashboard',
            'meta' => [],
        ],
    ]);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('inbox.data', 1)
            ->where('inbox.data.0.title', 'Team invitation'));
});

it('returns recent notifications as json', function () {
    $user = User::factory()->create();

    $user->notifications()->create([
        'id' => (string) Str::uuid7(),
        'type' => TeamInvitationNotification::class,
        'data' => [
            'kind' => 'team_invitation',
            'title' => 'Team invitation',
            'body' => 'You were invited.',
            'actionUrl' => '/dashboard',
            'meta' => [],
        ],
    ]);

    $this->actingAs($user)
        ->getJson(route('notifications.recent'))
        ->assertOk()
        ->assertJsonPath('unreadCount', 1)
        ->assertJsonCount(1, 'items');
});

it('marks a notification as read', function () {
    $user = User::factory()->create();

    $notification = $user->notifications()->create([
        'id' => (string) Str::uuid7(),
        'type' => TeamInvitationNotification::class,
        'data' => [
            'kind' => 'team_invitation',
            'title' => 'Team invitation',
            'body' => 'You were invited.',
            'actionUrl' => '/dashboard',
            'meta' => [],
        ],
    ]);

    $this->actingAs($user)
        ->patchJson(route('notifications.read', $notification))
        ->assertOk()
        ->assertJsonPath('unreadCount', 0);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('marks all notifications as read', function () {
    $user = User::factory()->create();

    collect(range(1, 2))->each(function () use ($user): void {
        $user->notifications()->create([
            'id' => (string) Str::uuid7(),
            'type' => TeamInvitationNotification::class,
            'data' => [
                'kind' => 'team_invitation',
                'title' => 'Team invitation',
                'body' => 'You were invited.',
                'actionUrl' => '/dashboard',
                'meta' => [],
            ],
        ]);
    });

    $this->actingAs($user)
        ->post(route('notifications.read-all'))
        ->assertRedirect();

    expect($user->unreadNotifications()->count())->toBe(0);
});
