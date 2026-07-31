<?php

namespace Tests\Feature\Admin;

use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlanPageGuidance;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Database\Seeders\SavingsPlanPageGuidanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingsPlanGuidanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            SavingsFormulaTemplateSeeder::class,
            SavingsPlanPageGuidanceSeeder::class,
            BillingSeeder::class,
        ]);
    }

    public function test_platform_admin_can_update_page_guidance(): void
    {
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

        $this->assertSame('Pick the formula that fits your goals.', $guidance->chooser_intro);
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $guidance->chooser_video_url);
    }

    public function test_non_admin_cannot_update_page_guidance(): void
    {
        $user = User::factory()->create(['is_platform_admin' => false]);

        $response = $this->actingAs($user)->put(route('admin.savings-plan-guidance.update'), [
            'chooser_intro' => 'Blocked',
        ]);

        $response->assertForbidden();
    }

    public function test_invalid_video_url_is_rejected(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);

        $response = $this->actingAs($admin)->put(route('admin.savings-plan-guidance.update'), [
            'chooser_video_url' => 'https://evil.example.com/video',
        ]);

        $response->assertSessionHasErrors('chooser_video_url');
    }

    public function test_platform_admin_can_update_formula_template_guidance(): void
    {
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

        $this->assertSame('Seven-fund TRC split', $template->description);
        $this->assertSame('Members who want detailed buckets', $template->best_for);
        $this->assertSame('Updated category purpose', $category->description);
    }

    public function test_savings_plan_page_includes_guidance_props(): void
    {
        $user = User::factory()->create();

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
    }
}
