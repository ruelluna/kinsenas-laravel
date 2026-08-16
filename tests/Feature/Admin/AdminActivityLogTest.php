<?php

use App\Enums\UserActivityAction;
use App\Models\User;
use App\Services\Audit\UserActivityLogger;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('forbids non platform admins from viewing activity logs', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.activity-logs.index'))
        ->assertForbidden();
});

it('shows paginated activity logs for platform admins', function () {
    $admin = User::factory()->platformAdmin()->create();
    $actor = User::factory()->create(['name' => 'Actor User']);

    app(UserActivityLogger::class)->log(
        UserActivityAction::TeamInvitationSent,
        'Sent team invitation to invited@example.com',
        $actor,
        properties: ['email' => 'invited@example.com'],
    );

    $this->actingAs($admin)
        ->get(route('admin.activity-logs.index', ['search' => 'Actor']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/activity-logs/index')
            ->has('activities.data', 1)
            ->where('activities.data.0.event', UserActivityAction::TeamInvitationSent->value));
});
