<?php

use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlanPageGuidance;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Database\Seeders\SavingsPlanPageGuidanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        SavingsPlanPageGuidanceSeeder::class,
        BillingSeeder::class,
    ]);
});

it('platform admin can update page guidance', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);

    $response = $this->actingAs($admin)->put(route('admin.savings-plan-guidance.update'), [
        'chooser_intro' => 'Pick the formula that fits your goals.',
        'chooser_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'before_choose_note' => 'One plan per team.',
        'after_income_rules' => 'Percentages lock after income.',
        'after_income_video_url' => null,
    ]);

    $response->assertRedirect();

    $guidance = SavingsPlanPageGuidance::instance();

    expect($guidance->chooser_intro)->toBe('Pick the formula that fits your goals.');
    expect($guidance->chooser_video_url)->toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
});

it('non admin cannot update page guidance', function () {
    $user = User::factory()->create(['is_platform_admin' => false]);

    $response = $this->actingAs($user)->put(route('admin.savings-plan-guidance.update'), [
        'chooser_intro' => 'Blocked',
    ]);

    $response->assertForbidden();
});

it('invalid video url is rejected', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);

    $response = $this->actingAs($admin)->put(route('admin.savings-plan-guidance.update'), [
        'chooser_video_url' => 'https://evil.example.com/video',
    ]);

    $response->assertSessionHasErrors('chooser_video_url');
});

it('platform admin can update formula template guidance', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();
    $category = $template->categories()->firstOrFail();

    $response = $this->actingAs($admin)->put(route('admin.formula-templates.update', $template), [
        'description' => 'Seven-fund TRC split',
        'best_for' => 'Members who want detailed buckets',
        'video_embed_url' => 'https://vimeo.com/123456789',
        'categories' => [
            [
                'id' => $category->id,
                'description' => 'Updated category purpose',
            ],
        ],
    ]);

    $response->assertRedirect();

    $template->refresh();
    $category->refresh();

    expect($template->description)->toBe('Seven-fund TRC split');
    expect($template->best_for)->toBe('Members who want detailed buckets');
    expect($category->description)->toBe('Updated category purpose');
});

it('savings plan page includes guidance props', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $response = $this->actingAs($user)->get(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('savings/plan')
        ->has('pageGuidance', fn ($guidance) => $guidance
            ->has('chooserIntro')
            ->has('chooserVideoUrl')
            ->has('beforeChooseNote')
            ->has('afterIncomeRules')
            ->has('afterIncomeVideoUrl'))
        ->has('templates', 2)
        ->has('templates.0.bestFor')
        ->has('templates.0.videoEmbedUrl')
        ->has('templates.0.categories.0.description'));
});
