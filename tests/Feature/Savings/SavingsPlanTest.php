<?php

use App\Models\Bank;
use App\Models\SavingsCategory;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

/**
 * @return array{0: User, 1: SavingsPlan}
 */
function createUserWithSavingsPlanAndIncome(): array
{
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    test()->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'January salary',
        'amount' => '50000.00',
        'period_start' => '2026-01-01',
    ]);

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();

    return [$user, $plan];
}

/**
 * @return list<array<string, mixed>>
 */
function categoriesPayload(SavingsPlan $plan): array
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

it('allows user to create savings plan from template', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
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
});

it('requires category percentages to total one hundred', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
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
});

it('can save plan with mixed percentage and deduction categories', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
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
});

it('rejects deduction from non-percentage category', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
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
});

it('can save custom category without default amount', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
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
});

it('encrypts income amount in database', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $this->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'January salary',
        'amount' => '50000.00',
        'period_start' => '2026-01-01',
    ]);

    $raw = \DB::table('income_periods')->value('amount_encrypted');

    $this->assertIsString($raw);
    $this->assertStringNotContainsString('50000', $raw);
});

it('cannot change percentages after income exists', function () {
    [$user, $plan] = createUserWithSavingsPlanAndIncome();
    $payload = categoriesPayload($plan);

    $payload[0]['percentage'] = 60;
    $payload[1]['percentage'] = 20;

    $response = $this->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => $payload,
    ]);

    $response->assertSessionHasErrors('categories.0.percentage');
});

it('cannot remove percentage category after income exists', function () {
    [$user, $plan] = createUserWithSavingsPlanAndIncome();
    $payload = categoriesPayload($plan);

    array_pop($payload);

    $response = $this->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => $payload,
    ]);

    $response->assertSessionHasErrors('categories');
});

it('can remove custom category after income exists', function () {
    [$user, $plan] = createUserWithSavingsPlanAndIncome();
    $payload = categoriesPayload($plan);

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
        categoriesPayload($plan),
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
});

it('can edit custom category after income exists', function () {
    [$user, $plan] = createUserWithSavingsPlanAndIncome();
    $payload = categoriesPayload($plan);

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

    $payload = categoriesPayload($plan);

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
});

it('can append custom category after income exists', function () {
    [$user, $plan] = createUserWithSavingsPlanAndIncome();
    $payload = categoriesPayload($plan);

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
});

it('cannot add percentage category after income exists', function () {
    [$user, $plan] = createUserWithSavingsPlanAndIncome();
    $payload = categoriesPayload($plan);

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
});

it('syncs category bank assignments on plan update', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();

    $bank = Bank::factory()->create([
        'team_id' => $user->currentTeam->id,
        'name' => 'BDO',
    ]);

    $payload = categoriesPayload($plan);
    $payload[0]['bank_id'] = $bank->id;

    $response = $this->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => $payload,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('savings_categories', [
        'plan_id' => $plan->id,
        'name' => 'Everyday Fund',
        'bank_id' => $bank->id,
    ]);
});

it('allows same bank on multiple categories', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();
    $bank = Bank::factory()->create([
        'team_id' => $user->currentTeam->id,
        'name' => 'BDO',
    ]);

    $payload = categoriesPayload($plan);
    $payload[0]['bank_id'] = $bank->id;
    $payload[1]['bank_id'] = $bank->id;

    $response = $this->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => $payload,
    ]);

    $response->assertRedirect();

    $this->assertEquals(2, SavingsCategory::query()->where('bank_id', $bank->id)->count());
});
