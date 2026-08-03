<?php

use App\Actions\Teams\CreateTeam;
use App\Enums\SubscriptionStatus;
use App\Enums\TeamRole;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('grants platform admin access to another user', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    $member = User::factory()->create(['email' => 'member@example.com', 'is_platform_admin' => false]);

    $response = $this->actingAs($admin)->patch(route('admin.platform-users.update', $member), [
        'is_platform_admin' => true,
    ]);

    $response->assertRedirect();

    expect($member->fresh()->is_platform_admin)->toBeTrue();
});

it('revokes platform admin access from another user', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    $otherAdmin = User::factory()->create(['email' => 'other-admin@example.com', 'is_platform_admin' => true]);

    $response = $this->actingAs($admin)->patch(route('admin.platform-users.update', $otherAdmin), [
        'is_platform_admin' => false,
    ]);

    $response->assertRedirect();

    expect($otherAdmin->fresh()->is_platform_admin)->toBeFalse();
});

it('blocks revoking own platform admin access', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);

    $response = $this->actingAs($admin)->patch(route('admin.platform-users.update', $admin), [
        'is_platform_admin' => false,
    ]);

    $response->assertSessionHasErrors('is_platform_admin');

    expect($admin->fresh()->is_platform_admin)->toBeTrue();
});

it('allows revoking platform admin when another admin remains', function () {
    User::query()->where('is_platform_admin', true)->update(['is_platform_admin' => false]);

    $soleAdmin = User::factory()->create(['email' => 'sole-admin@example.com', 'is_platform_admin' => true]);
    $actor = User::factory()->create(['email' => 'actor@example.com', 'is_platform_admin' => true]);

    $response = $this->actingAs($actor)->patch(route('admin.platform-users.update', $soleAdmin), [
        'is_platform_admin' => false,
    ]);

    $response->assertRedirect();

    expect($soleAdmin->fresh()->is_platform_admin)->toBeFalse();
});

it('forbids non-admin from updating platform admin status', function () {
    $member = User::factory()->create(['email' => 'member@example.com', 'is_platform_admin' => false]);
    $target = User::factory()->create(['email' => 'target@example.com', 'is_platform_admin' => false]);

    $response = $this->actingAs($member)->patch(route('admin.platform-users.update', $target), [
        'is_platform_admin' => true,
    ]);

    $response->assertForbidden();
});

it('removes a user account as platform admin', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    $member = User::factory()->create(['email' => 'remove-me@example.com', 'is_platform_admin' => false]);
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
    $admin = User::factory()->create(['email' => 'admin-self@example.com', 'is_platform_admin' => true]);

    $response = $this->actingAs($admin)->delete(route('admin.platform-users.destroy', $admin), [
        'email' => 'admin-self@example.com',
    ]);

    $response->assertSessionHasErrors('email');

    expect($admin->fresh())->not->toBeNull();
});

it('allows deleting platform admin when another admin remains', function () {
    User::query()->where('is_platform_admin', true)->update(['is_platform_admin' => false]);

    $soleAdmin = User::factory()->create(['email' => 'sole-admin@example.com', 'is_platform_admin' => true]);
    $actor = User::factory()->create(['email' => 'actor@example.com', 'is_platform_admin' => true]);

    $response = $this->actingAs($actor)->delete(route('admin.platform-users.destroy', $soleAdmin), [
        'email' => 'sole-admin@example.com',
    ]);

    $response->assertRedirect(route('admin.platform-users.index'));

    expect(User::query()->find($soleAdmin->id))->toBeNull();
});

it('blocks deleting a user who owns a shared team with other members', function () {
    config(['teams.allow_additional_owned_teams' => true]);

    $admin = User::factory()->create(['is_platform_admin' => true]);
    $owner = User::factory()->create(['email' => 'shared-owner@example.com', 'is_platform_admin' => false]);
    $member = User::factory()->create(['email' => 'shared-member@example.com', 'is_platform_admin' => false]);

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

    $admin = User::factory()->create(['is_platform_admin' => true]);
    $owner = User::factory()->create(['email' => 'solo-owner@example.com', 'is_platform_admin' => false]);

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
    $admin = User::factory()->create(['is_platform_admin' => true]);
    $member = User::factory()->create(['email' => 'confirm@example.com', 'is_platform_admin' => false]);

    $response = $this->actingAs($admin)->delete(route('admin.platform-users.destroy', $member), [
        'email' => 'wrong@example.com',
    ]);

    $response->assertSessionHasErrors('email');

    expect($member->fresh())->not->toBeNull();
});

it('forbids non-admin from deleting users', function () {
    $member = User::factory()->create(['email' => 'member@example.com', 'is_platform_admin' => false]);
    $target = User::factory()->create(['email' => 'target@example.com', 'is_platform_admin' => false]);

    $response = $this->actingAs($member)->delete(route('admin.platform-users.destroy', $target), [
        'email' => 'target@example.com',
    ]);

    $response->assertForbidden();
});
