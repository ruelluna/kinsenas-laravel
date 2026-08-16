<?php

use App\Actions\Teams\CreateTeam;
use App\Enums\PlatformRole;
use App\Enums\SubscriptionStatus;
use App\Enums\TeamRole;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('grants author role to another user', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);

    $response = $this->actingAs($admin)->patch(route('admin.platform-users.update', $member), [
        'role' => PlatformRole::Author->value,
    ]);

    $response->assertRedirect();

    expect($member->fresh()->isAuthor())->toBeTrue();
});

it('changes platform admin to user role', function () {
    $admin = User::factory()->platformAdmin()->create();
    $otherAdmin = User::factory()->platformAdmin()->create(['email' => 'other-admin@example.com']);

    $response = $this->actingAs($admin)->patch(route('admin.platform-users.update', $otherAdmin), [
        'role' => PlatformRole::User->value,
    ]);

    $response->assertRedirect();

    expect($otherAdmin->fresh()->hasRole(PlatformRole::User->value))->toBeTrue()
        ->and($otherAdmin->fresh()->isPlatformAdmin())->toBeFalse();
});

it('blocks changing own platform role', function () {
    $admin = User::factory()->platformAdmin()->create();

    $response = $this->actingAs($admin)->patch(route('admin.platform-users.update', $admin), [
        'role' => PlatformRole::User->value,
    ]);

    $response->assertSessionHasErrors('role');

    expect($admin->fresh()->isPlatformAdmin())->toBeTrue();
});

it('allows demoting platform admin when another admin remains', function () {
    User::query()->each(fn (User $user) => $user->syncPlatformRole(PlatformRole::User));

    $soleAdmin = User::factory()->platformAdmin()->create(['email' => 'sole-admin@example.com']);
    $actor = User::factory()->platformAdmin()->create(['email' => 'actor@example.com']);

    $response = $this->actingAs($actor)->patch(route('admin.platform-users.update', $soleAdmin), [
        'role' => PlatformRole::User->value,
    ]);

    $response->assertRedirect();

    expect($soleAdmin->fresh()->isPlatformAdmin())->toBeFalse();
});

it('forbids non-admin from updating platform roles', function () {
    $member = User::factory()->create(['email' => 'member@example.com']);
    $target = User::factory()->create(['email' => 'target@example.com']);

    $response = $this->actingAs($member)->patch(route('admin.platform-users.update', $target), [
        'role' => PlatformRole::Author->value,
    ]);

    $response->assertForbidden();
});

it('removes a user account as platform admin', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'remove-me@example.com']);
    $memberId = $member->id;
    $personalTeamId = $member->personalTeam()->id;

    $response = $this->actingAs($admin)->delete(route('admin.platform-users.destroy', $member), [
        'email' => 'remove-me@example.com',
    ]);

    $response->assertRedirect(route('admin.platform-users.index'));

    $this->assertDatabaseMissing('users', ['id' => $memberId]);
    $this->assertSoftDeleted('teams', ['id' => $personalTeamId]);
});

it('blocks deleting own account from admin', function () {
    $admin = User::factory()->platformAdmin()->create(['email' => 'admin-self@example.com']);

    $response = $this->actingAs($admin)->delete(route('admin.platform-users.destroy', $admin), [
        'email' => 'admin-self@example.com',
    ]);

    $response->assertSessionHasErrors('email');

    expect($admin->fresh())->not->toBeNull();
});

it('allows deleting platform admin when another admin remains', function () {
    User::query()->each(fn (User $user) => $user->syncPlatformRole(PlatformRole::User));

    $soleAdmin = User::factory()->platformAdmin()->create(['email' => 'sole-admin@example.com']);
    $actor = User::factory()->platformAdmin()->create(['email' => 'actor@example.com']);

    $response = $this->actingAs($actor)->delete(route('admin.platform-users.destroy', $soleAdmin), [
        'email' => 'sole-admin@example.com',
    ]);

    $response->assertRedirect(route('admin.platform-users.index'));

    expect(User::query()->find($soleAdmin->id))->toBeNull();
});

it('blocks deleting a user who owns a shared team with other members', function () {
    config(['teams.allow_additional_owned_teams' => true]);

    $admin = User::factory()->platformAdmin()->create();
    $owner = User::factory()->create(['email' => 'shared-owner@example.com']);
    $member = User::factory()->create(['email' => 'shared-member@example.com']);

    $sharedTeam = app(CreateTeam::class)->handle($owner, 'Shared Team', isPersonal: false);
    $sharedTeam->members()->attach($member, ['role' => TeamRole::Member->value]);

    $response = $this->actingAs($admin)->delete(route('admin.platform-users.destroy', $owner), [
        'email' => 'shared-owner@example.com',
    ]);

    $response->assertSessionHasErrors('email');

    expect($owner->fresh())->not->toBeNull();
});

it('cancels subscription when deleting sole owner of a shared team', function () {
    config(['teams.allow_additional_owned_teams' => true]);

    $admin = User::factory()->platformAdmin()->create();
    $owner = User::factory()->create(['email' => 'solo-owner@example.com']);

    $sharedTeam = app(CreateTeam::class)->handle($owner, 'Solo Shared Team', isPersonal: false);
    $subscriptionId = $sharedTeam->subscription->id;
    $ownerId = $owner->id;

    $response = $this->actingAs($admin)->delete(route('admin.platform-users.destroy', $owner), [
        'email' => 'solo-owner@example.com',
    ]);

    $response->assertRedirect(route('admin.platform-users.index'));

    $this->assertDatabaseMissing('users', ['id' => $ownerId]);
    $this->assertDatabaseHas('subscriptions', [
        'id' => $subscriptionId,
        'status' => SubscriptionStatus::Cancelled->value,
    ]);
});

it('requires matching email confirmation to delete a user', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'confirm@example.com']);

    $response = $this->actingAs($admin)->delete(route('admin.platform-users.destroy', $member), [
        'email' => 'wrong@example.com',
    ]);

    $response->assertSessionHasErrors('email');

    expect($member->fresh())->not->toBeNull();
});

it('forbids non-admin from deleting users', function () {
    $member = User::factory()->create(['email' => 'member@example.com']);
    $target = User::factory()->create(['email' => 'target@example.com']);

    $response = $this->actingAs($member)->delete(route('admin.platform-users.destroy', $target), [
        'email' => 'target@example.com',
    ]);

    $response->assertForbidden();
});
