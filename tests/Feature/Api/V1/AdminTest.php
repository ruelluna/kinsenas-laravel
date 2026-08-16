<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

it('forbids admin routes for non platform admins', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/admin/subscribers');

    $response->assertForbidden();
});

it('allows admin routes for platform admins', function () {
    $admin = User::factory()->platformAdmin()->create();
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/admin/subscribers');

    $response->assertSuccessful();
    $response->assertJsonStructure(['data', 'links', 'meta']);
});

it('lists platform users for admins', function () {
    $admin = User::factory()->platformAdmin()->create();
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/admin/platform-users');

    $response->assertSuccessful();
    $response->assertJsonStructure(['data', 'meta']);
});
