<?php

use App\Models\SavingsFormulaTemplate;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    fakeGhlApi();

    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

it('adds abundant plan chosen tag when abundant template is selected', function () {
    $user = User::factory()->create(['email' => 'abundant@example.com']);
    $this->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]))->assertRedirect();

    Http::assertSent(function ($request) {
        $tags = collect($request->data()['tags'] ?? []);

        return $request->method() === 'POST'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('plan-created')
            && $tags->contains('abundant-plan-chosen');
    });

    Http::assertSent(function ($request) {
        $tags = collect($request->data()['tags'] ?? []);

        return $request->method() === 'DELETE'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('trc-plan-chosen')
            && $tags->contains('custom-plan-chosen');
    });
});

it('adds trc plan chosen tag when trc template is selected', function () {
    $user = User::factory()->create(['email' => 'trc@example.com']);
    $this->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]))->assertRedirect();

    Http::assertSent(function ($request) {
        $tags = collect($request->data()['tags'] ?? []);

        return $request->method() === 'POST'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('trc-plan-chosen');
    });
});

it('adds custom plan chosen tag when custom plan is created', function () {
    $user = User::factory()->create(['email' => 'custom@example.com']);
    $this->unlockVaultFor($user);

    $this->actingAs($user)->post(route('savings.plan.custom', [
        'current_team' => $user->currentTeam->slug,
    ]))->assertRedirect();

    Http::assertSent(function ($request) {
        $tags = collect($request->data()['tags'] ?? []);

        return $request->method() === 'POST'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('plan-created')
            && $tags->contains('custom-plan-chosen');
    });
});
