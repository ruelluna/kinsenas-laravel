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

it('allows user to create a custom savings plan without template categories', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $response = $this
        ->actingAs($user)
        ->post(route('savings.plan.custom', [
            'current_team' => $user->currentTeam->slug,
        ]));

    $response->assertRedirect();
    $this->assertDatabaseHas('savings_plans', [
        'team_id' => $user->currentTeam->id,
        'created_by_user_id' => $user->id,
    ]);
    $this->assertDatabaseCount('savings_categories', 0);
});

it('plan chooser includes empty team banks for banks-first soft gate', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $response = $this->actingAs($user)->get(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('savings/plan')
        ->where('plan', null)
        ->has('teamBanks', 0),
    );
});

it('plan chooser includes team banks after banks are added', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    Bank::factory()->create([
        'team_id' => $user->currentTeam->id,
        'name' => 'BDO Checking',
    ]);

    $response = $this->actingAs($user)->get(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('savings/plan')
        ->has('teamBanks', 1)
        ->where('teamBanks.0.name', 'BDO Checking'),
    );
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

    $raw = DB::table('income_periods')->value('amount_encrypted');

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

it('saves opening balances on plan update before income', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();
    $payload = categoriesPayload($plan);
    $payload[0]['opening_balance'] = '25000.00';
    $payload[1]['opening_balance'] = '10000.00';

    $response = $this->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => $payload,
    ]);

    $response->assertRedirect();

    $everyday = $plan->fresh('categories')->categories->firstWhere('name', 'Everyday Fund');
    $savings = $plan->fresh('categories')->categories->firstWhere('name', 'Savings');

    expect($everyday?->opening_balance_encrypted)->toBe('25000.00')
        ->and($savings?->opening_balance_encrypted)->toBe('10000.00');

    $showResponse = $this->actingAs($user)->get(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $showResponse->assertOk();
    $showResponse->assertInertia(fn ($page) => $page
        ->has('fundBalances', 3)
        ->where('fundBalances.0.name', 'Everyday Fund')
        ->where('fundBalances.0.openingBalance', '25000.00')
        ->where('fundBalances.0.remaining', '25000.00'),
    );
});

it('rejects opening balance changes after income is recorded', function () {
    [$user, $plan] = createUserWithSavingsPlanAndIncome();
    $payload = categoriesPayload($plan);
    $payload[0]['opening_balance'] = '99999.00';

    $response = $this->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => $payload,
    ]);

    $response->assertSessionHasErrors('categories.0.opening_balance');
});

it('adds opening balance to a fund bucket before income', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

    $response = $this->actingAs($user)->patch(route('savings.plan.category.opening-balance', [
        'current_team' => $user->currentTeam->slug,
        'category' => $everyday->id,
    ]), [
        'amount' => '5000.00',
    ]);

    $response->assertRedirect();

    expect($everyday->fresh()->opening_balance_encrypted)->toBe('5000.00');
});

it('preserves opening balances when saving the plan without resubmitting them', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

    $this->actingAs($user)->patch(route('savings.plan.category.opening-balance', [
        'current_team' => $user->currentTeam->slug,
        'category' => $everyday->id,
    ]), [
        'amount' => '5000.00',
    ]);

    $payload = categoriesPayload($plan->fresh('categories'));

    $response = $this->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => $payload,
    ]);

    $response->assertRedirect();

    $updatedEveryday = SavingsPlan::query()->firstOrFail()
        ->categories()
        ->where('name', 'Everyday Fund')
        ->firstOrFail();

    expect($updatedEveryday->opening_balance_encrypted)->toBe('5000.00');
});

it('shows fund balances on plan page before opening balances are added', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $response = $this->actingAs($user)->get(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('fundBalances', 3)
        ->where('fundBalances.0.canFund', true)
        ->where('fundBalances.0.remaining', '0.00'),
    );
});

it('adds opening balance to a fund bucket after income is recorded', function () {
    [$user, $plan] = createUserWithSavingsPlanAndIncome();
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

    $response = $this->actingAs($user)->patch(route('savings.plan.category.opening-balance', [
        'current_team' => $user->currentTeam->slug,
        'category' => $everyday->id,
    ]), [
        'amount' => '3000.00',
    ]);

    $response->assertRedirect();

    expect($everyday->fresh()->opening_balance_encrypted)->toBe('3000.00');
});

it('adds opening balance to a custom deduction fund bucket', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $this->actingAs($user)->put(route('savings.plan.update', [
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

    $collegeFund = SavingsPlan::query()->firstOrFail()
        ->categories()
        ->where('name', 'College Fund')
        ->firstOrFail();

    $response = $this->actingAs($user)->patch(route('savings.plan.category.opening-balance', [
        'current_team' => $user->currentTeam->slug,
        'category' => $collegeFund->id,
    ]), [
        'amount' => '2500.00',
    ]);

    $response->assertRedirect();

    expect($collegeFund->fresh()->opening_balance_encrypted)->toBe('2500.00');
});

it('allows discarding plan before income to return to formula chooser', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $this->assertDatabaseCount('savings_plans', 1);

    $response = $this->actingAs($user)->delete(route('savings.plan.destroy', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertRedirect(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));
    $this->assertDatabaseCount('savings_plans', 0);
    $this->assertDatabaseCount('savings_categories', 0);

    $showResponse = $this->actingAs($user)->get(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $showResponse->assertOk();
    $showResponse->assertInertia(fn ($page) => $page
        ->component('savings/plan')
        ->where('plan', null),
    );
});

it('cannot discard plan after income exists', function () {
    [$user] = createUserWithSavingsPlanAndIncome();

    $response = $this->actingAs($user)->delete(route('savings.plan.destroy', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertForbidden();
    $this->assertDatabaseCount('savings_plans', 1);
});
