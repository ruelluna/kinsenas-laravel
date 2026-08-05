<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

it('returns a token for valid credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'test',
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure(['token', 'user' => ['id', 'email']]);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable();
});

it('returns shared props for authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertSuccessful();
    $response->assertJsonPath('user.email', $user->email);
    $response->assertJsonStructure(['teams', 'vaultLocked', 'subscription']);
});

it('revokes token on logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/v1/auth/logout');

    $response->assertNoContent();
    expect($user->tokens()->count())->toBe(0);
});

it('registers a new user and returns token with recovery key', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'New Member',
        'email' => 'new@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'device_name' => 'test',
    ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'token',
        'recovery_key',
        'user' => ['id', 'email', 'name'],
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'new@example.com',
    ]);
});

it('returns register context props', function () {
    $response = $this->getJson('/api/v1/auth/register-context');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'passwordRules',
        'teamInvitation',
        'openBetaOffer',
        'betaCode',
        'betaCodeLabel',
    ]);
});

it('sends password reset link', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure(['message']);
});
