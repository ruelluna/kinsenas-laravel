<?php

namespace Tests\Feature\Savings;

use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;
use Tests\TestCase;

class SavingsPlanTest extends TestCase
{
    use RefreshDatabase, UnlocksVault;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            SavingsFormulaTemplateSeeder::class,
            BillingSeeder::class,
        ]);
    }

    public function test_user_can_create_savings_plan_from_template(): void
    {
        $user = User::factory()->create();
        $this->unlockVaultFor($user);
        $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

        $response = $this
            ->actingAs($user)
            ->post(route('savings.plan.from-template', [
                'current_team' => $user->currentTeam->slug,
                'template' => $template->id,
            ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('savings_plans', [
            'team_id' => $user->currentTeam->id,
            'created_by_user_id' => $user->id,
        ]);
        $this->assertDatabaseCount('savings_categories', 7);
    }

    public function test_category_percentages_must_total_one_hundred(): void
    {
        $user = User::factory()->create();
        $this->unlockVaultFor($user);
        $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

        $this->actingAs($user)->post(route('savings.plan.from-template', [
            'current_team' => $user->currentTeam->slug,
            'template' => $template->id,
        ]));

        $response = $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => [
                ['name' => 'A', 'allocation_type' => 'percentage', 'percentage' => 50],
                ['name' => 'B', 'allocation_type' => 'percentage', 'percentage' => 40],
            ],
        ]);

        $response->assertSessionHasErrors('categories');
    }

    public function test_can_save_plan_with_mixed_percentage_and_deduction_categories(): void
    {
        $user = User::factory()->create();
        $this->unlockVaultFor($user);
        $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

        $this->actingAs($user)->post(route('savings.plan.from-template', [
            'current_team' => $user->currentTeam->slug,
            'template' => $template->id,
        ]));

        $response = $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => [
                ['name' => 'Everyday Fund', 'allocation_type' => 'percentage', 'percentage' => 50],
                ['name' => 'Savings', 'allocation_type' => 'percentage', 'percentage' => 30],
                ['name' => 'Tithe', 'allocation_type' => 'percentage', 'percentage' => 20],
                [
                    'name' => 'College Fund',
                    'allocation_type' => 'deduction',
                    'deduction_mode' => 'fixed',
                    'deduction_value' => 1000,
                    'deduct_from_index' => 0,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('savings_categories', [
            'name' => 'College Fund',
            'allocation_type' => 'deduction',
            'deduction_mode' => 'fixed',
            'deduction_value' => '1000.00',
        ]);
    }

    public function test_rejects_deduction_from_non_percentage_category(): void
    {
        $user = User::factory()->create();
        $this->unlockVaultFor($user);
        $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

        $this->actingAs($user)->post(route('savings.plan.from-template', [
            'current_team' => $user->currentTeam->slug,
            'template' => $template->id,
        ]));

        $response = $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => [
                ['name' => 'Everyday Fund', 'allocation_type' => 'percentage', 'percentage' => 100],
                [
                    'name' => 'College Fund',
                    'allocation_type' => 'deduction',
                    'deduction_mode' => 'fixed',
                    'deduction_value' => 500,
                    'deduct_from_index' => 0,
                ],
                [
                    'name' => 'Other Fund',
                    'allocation_type' => 'deduction',
                    'deduction_mode' => 'fixed',
                    'deduction_value' => 200,
                    'deduct_from_index' => 1,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('categories.2.deduct_from_index');
    }

    public function test_can_save_custom_category_without_default_amount(): void
    {
        $user = User::factory()->create();
        $this->unlockVaultFor($user);
        $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

        $this->actingAs($user)->post(route('savings.plan.from-template', [
            'current_team' => $user->currentTeam->slug,
            'template' => $template->id,
        ]));

        $response = $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => [
                ['name' => 'Everyday Fund', 'allocation_type' => 'percentage', 'percentage' => 50],
                ['name' => 'Savings', 'allocation_type' => 'percentage', 'percentage' => 30],
                ['name' => 'Tithe', 'allocation_type' => 'percentage', 'percentage' => 20],
                [
                    'name' => 'College Fund',
                    'allocation_type' => 'deduction',
                    'deduct_from_index' => 0,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('savings_categories', [
            'name' => 'College Fund',
            'allocation_type' => 'deduction',
            'deduction_mode' => null,
            'deduction_value' => null,
        ]);
    }

    public function test_income_amount_is_encrypted_in_database(): void
    {
        $user = User::factory()->create();
        $this->unlockVaultFor($user);
        $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

        $this->actingAs($user)->post(route('savings.plan.from-template', [
            'current_team' => $user->currentTeam->slug,
            'template' => $template->id,
        ]));

        $this->actingAs($user)->post(route('savings.income.store', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'amount' => '50000.00',
            'period_start' => '2026-01-01',
        ]);

        $raw = \DB::table('income_periods')->value('amount_encrypted');

        $this->assertIsString($raw);
        $this->assertStringNotContainsString('50000', $raw);
    }

    public function test_cannot_change_percentages_after_income_exists(): void
    {
        [$user, $plan] = $this->createUserWithPlanAndIncome();
        $payload = $this->categoriesPayload($plan);

        $payload[0]['percentage'] = 60;
        $payload[1]['percentage'] = 20;

        $response = $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => $payload,
        ]);

        $response->assertSessionHasErrors('categories.0.percentage');
    }

    public function test_cannot_remove_percentage_category_after_income_exists(): void
    {
        [$user, $plan] = $this->createUserWithPlanAndIncome();
        $payload = $this->categoriesPayload($plan);

        array_pop($payload);

        $response = $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => $payload,
        ]);

        $response->assertSessionHasErrors('categories');
    }

    public function test_can_remove_custom_category_after_income_exists(): void
    {
        [$user, $plan] = $this->createUserWithPlanAndIncome();
        $payload = $this->categoriesPayload($plan);

        $payload[] = [
            'name' => 'College Fund',
            'allocation_type' => 'deduction',
            'deduct_from_index' => 0,
        ];

        $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => $payload,
        ]);

        $plan->refresh()->load('categories');
        $collegeFund = $plan->categories->firstWhere('name', 'College Fund');
        $this->assertNotNull($collegeFund);

        $payload = array_values(array_filter(
            $this->categoriesPayload($plan),
            fn (array $category) => ($category['id'] ?? null) !== $collegeFund->id,
        ));

        $response = $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => $payload,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('savings_categories', [
            'id' => $collegeFund->id,
        ]);
        $this->assertDatabaseCount('savings_categories', 3);
    }

    public function test_can_edit_custom_category_after_income_exists(): void
    {
        [$user, $plan] = $this->createUserWithPlanAndIncome();
        $payload = $this->categoriesPayload($plan);

        $payload[] = [
            'name' => 'College Fund',
            'allocation_type' => 'deduction',
            'deduction_mode' => 'fixed',
            'deduction_value' => 1000,
            'deduct_from_index' => 0,
        ];

        $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => $payload,
        ]);

        $plan->refresh()->load('categories');
        $collegeFund = $plan->categories->firstWhere('name', 'College Fund');
        $this->assertNotNull($collegeFund);

        $payload = $this->categoriesPayload($plan);

        foreach ($payload as $index => $category) {
            if (($category['id'] ?? null) === $collegeFund->id) {
                $payload[$index]['name'] = 'Updated College Fund';
                $payload[$index]['deduction_value'] = '1500';
            }
        }

        $response = $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => $payload,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('savings_categories', [
            'id' => $collegeFund->id,
            'name' => 'Updated College Fund',
            'deduction_value' => '1500.00',
        ]);
    }

    public function test_can_append_custom_category_after_income_exists(): void
    {
        [$user, $plan] = $this->createUserWithPlanAndIncome();
        $payload = $this->categoriesPayload($plan);

        $payload[] = [
            'name' => 'College Fund',
            'allocation_type' => 'deduction',
            'deduct_from_index' => 0,
        ];

        $response = $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => $payload,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('savings_categories', [
            'plan_id' => $plan->id,
            'name' => 'College Fund',
            'allocation_type' => 'deduction',
        ]);
        $this->assertDatabaseCount('savings_categories', 4);
    }

    public function test_cannot_add_percentage_category_after_income_exists(): void
    {
        [$user, $plan] = $this->createUserWithPlanAndIncome();
        $payload = $this->categoriesPayload($plan);

        $payload[] = [
            'name' => 'Extra Fund',
            'allocation_type' => 'percentage',
            'percentage' => 10,
        ];

        $response = $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => $payload,
        ]);

        $response->assertSessionHasErrors('categories.3.allocation_type');
    }

    /**
     * @return array{0: User, 1: SavingsPlan}
     */
    private function createUserWithPlanAndIncome(): array
    {
        $user = User::factory()->create();
        $this->unlockVaultFor($user);
        $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

        $this->actingAs($user)->post(route('savings.plan.from-template', [
            'current_team' => $user->currentTeam->slug,
            'template' => $template->id,
        ]));

        $this->actingAs($user)->post(route('savings.income.store', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'amount' => '50000.00',
            'period_start' => '2026-01-01',
        ]);

        $plan = SavingsPlan::query()->with('categories')->firstOrFail();

        return [$user, $plan];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function categoriesPayload(SavingsPlan $plan): array
    {
        $sorted = $plan->categories->sortBy('sort_order')->values();

        return $sorted->map(function ($category, $index) use ($sorted) {
            $payload = [
                'id' => $category->id,
                'name' => $category->name,
                'allocation_type' => $category->allocation_type->value,
            ];

            if ($category->allocation_type->value === 'percentage') {
                $payload['percentage'] = (string) $category->percentage;

                return $payload;
            }

            $payload['deduction_mode'] = $category->deduction_mode?->value;
            $payload['deduction_value'] = $category->deduction_value !== null
                ? (string) $category->deduction_value
                : null;

            if ($category->deduct_from_category_id !== null) {
                $sourceIndex = $sorted->search(
                    fn ($item) => $item->id === $category->deduct_from_category_id,
                );
                $payload['deduct_from_index'] = $sourceIndex !== false ? $sourceIndex : 0;
            }

            return $payload;
        })->all();
    }
}
