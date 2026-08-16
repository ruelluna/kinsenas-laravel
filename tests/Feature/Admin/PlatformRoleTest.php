<?php

use App\Enums\PlatformRole;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('assigns the user role to newly registered users', function () {
    $user = User::factory()->create();

    expect($user->hasRole(PlatformRole::User->value))->toBeTrue();
});

it('grants platform admin role via factory state', function () {
    $admin = User::factory()->platformAdmin()->create();

    expect($admin->isPlatformAdmin())->toBeTrue()
        ->and($admin->canManagePlatform())->toBeTrue()
        ->and($admin->canManageContent())->toBeTrue();
});

it('grants author role content access but not platform management', function () {
    $author = User::factory()->author()->create();

    expect($author->isAuthor())->toBeTrue()
        ->and($author->canManageContent())->toBeTrue()
        ->and($author->canManagePlatform())->toBeFalse();
});

it('allows platform admin to access content admin routes', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->get(route('admin.content.posts.index'))
        ->assertOk();
});

it('allows author to access content admin routes', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)
        ->get(route('admin.content.posts.index'))
        ->assertOk();
});

it('forbids author from accessing platform ops routes', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)
        ->get(route('admin.subscribers.index'))
        ->assertForbidden();
});

it('forbids regular users from admin routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.content.posts.index'))
        ->assertForbidden();
});

it('updates a user platform role', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);

    $response = $this->actingAs($admin)->patch(route('admin.platform-users.update', $member), [
        'role' => PlatformRole::Author->value,
    ]);

    $response->assertRedirect();

    expect($member->fresh()->isAuthor())->toBeTrue();
});

it('blocks changing own platform role', function () {
    $admin = User::factory()->platformAdmin()->create();

    $response = $this->actingAs($admin)->patch(route('admin.platform-users.update', $admin), [
        'role' => PlatformRole::User->value,
    ]);

    $response->assertSessionHasErrors('role');

    expect($admin->fresh()->isPlatformAdmin())->toBeTrue();
});
