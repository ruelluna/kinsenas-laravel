<?php

use App\Models\SavingsFormulaTemplate;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

it('seeds the simple plan template with everyday fund and savings buckets', function () {
    $template = SavingsFormulaTemplate::query()->where('slug', 'simple-plan')->firstOrFail();

    expect($template->name)->toBe('The Simple Plan')
        ->and($template->description)->toBe('No, not the pop-punk band.')
        ->and($template->sort_order)->toBe(0)
        ->and($template->categories()->pluck('name')->all())->toBe([
            'Everyday Fund',
            'Savings',
        ])
        ->and($template->categories()->where('name', 'Everyday Fund')->value('percentage'))
        ->toBe('80.00')
        ->and($template->categories()->where('name', 'Savings')->value('percentage'))
        ->toBe('20.00');
});

it('orders formula templates simple plan first on the savings plan chooser', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $response = $this->actingAs($user)->get(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/plan')
        ->where('templates.0.slug', 'simple-plan')
        ->where('templates.0.name', 'The Simple Plan')
        ->where('templates.1.slug', 'abundant-formula')
        ->where('templates.2.slug', 'trc-savings')
    );
});

it('creates a plan from the simple plan template', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', 'simple-plan')->firstOrFail();

    $response = $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $response->assertRedirect();

    expect($user->currentTeam->fresh())
        ->not->toBeNull();

    $this->assertDatabaseHas('savings_plans', [
        'team_id' => $user->currentTeam->id,
        'name' => 'The Simple Plan',
    ]);

    $this->assertDatabaseHas('savings_categories', [
        'name' => 'Everyday Fund',
        'percentage' => '80.00',
    ]);

    $this->assertDatabaseHas('savings_categories', [
        'name' => 'Savings',
        'percentage' => '20.00',
    ]);
});
