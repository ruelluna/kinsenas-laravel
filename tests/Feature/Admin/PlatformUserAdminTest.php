<?php

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

it('blocks revoking the last platform admin', function () {
    User::query()->where('is_platform_admin', true)->update(['is_platform_admin' => false]);

    $soleAdmin = User::factory()->create(['email' => 'sole-admin@example.com', 'is_platform_admin' => true]);
    $actor = User::factory()->create(['email' => 'actor@example.com', 'is_platform_admin' => true]);

    $response = $this->actingAs($actor)->patch(route('admin.platform-users.update', $soleAdmin), [
        'is_platform_admin' => false,
    ]);

    $response->assertSessionHasErrors('is_platform_admin');

    expect($soleAdmin->fresh()->is_platform_admin)->toBeTrue();
});

it('forbids non-admin from updating platform admin status', function () {
    $member = User::factory()->create(['email' => 'member@example.com', 'is_platform_admin' => false]);
    $target = User::factory()->create(['email' => 'target@example.com', 'is_platform_admin' => false]);

    $response = $this->actingAs($member)->patch(route('admin.platform-users.update', $target), [
        'is_platform_admin' => true,
    ]);

    $response->assertForbidden();
});
