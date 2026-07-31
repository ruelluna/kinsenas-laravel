<?php

use App\Models\FundSpend;
use App\Models\IncomePeriod;
use App\Models\SavingsCategory;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

function createUserWithLockedIncome(string $amount = '50000.00'): array
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
        'amount' => $amount,
        'period_start' => '2026-01-01',
    ]);

    $period = IncomePeriod::query()->firstOrFail();

    test()->actingAs($user)->post(route('savings.income.lock', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $plan = SavingsPlan::query()->firstOrFail();
    $everydayCategory = $plan->categories()->where('name', 'Everyday Fund')->firstOrFail();

    return [$user, $plan, $everydayCategory, $period];
}

it('shows spending page with fund balances', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();

    $response = $this->actingAs($user)->get(route('savings.spending.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/spending/index')
        ->where('defaultCategoryId', $everydayCategory->id)
        ->where('categories.0.id', $everydayCategory->id)
        ->has('fundBalances', 3)
        ->where('fundBalances.0.name', 'Everyday Fund')
        ->where('fundBalances.0.allocated', '35000.00')
        ->where('fundBalances.0.spent', '0.00')
        ->where('fundBalances.0.transferred', '0.00')
        ->where('fundBalances.0.remaining', '35000.00'),
    );
});

it('records quick spending and reduces remaining balance', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();

    $response = $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '500.00',
        'description' => 'Groceries',
        'spent_on' => '2026-01-15',
    ]);

    $response->assertRedirect();

    expect(FundSpend::query()->count())->toBe(1);

    $showResponse = $this->actingAs($user)->get(route('savings.spending.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $showResponse->assertInertia(fn (Assert $page) => $page
        ->where('fundBalances.0.spent', '500.00')
        ->where('fundBalances.0.remaining', '34500.00')
        ->has('spends', 1)
        ->where('spends.0.description', 'Groceries'),
    );
});

it('rejects spending above remaining balance', function () {
    [$user, , $everydayCategory] = createUserWithLockedIncome();

    $response = $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '40000.00',
        'description' => 'Too much',
        'spent_on' => '2026-01-15',
    ]);

    $response->assertSessionHasErrors('amount');
    expect(FundSpend::query()->count())->toBe(0);
});

it('creates pending spending when bank is provided', function () {
    [$user, , $everydayCategory] = createUserWithLockedIncome();

    $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'BPI',
        'account_label' => 'Everyday',
    ]);

    $bankId = \App\Models\Bank::query()->firstOrFail()->id;

    $this->actingAs($user)->post(route('savings.recipients.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'type' => 'person',
        'name' => 'Mechanic',
    ]);

    $recipientId = \App\Models\Recipient::query()->firstOrFail()->id;

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '5000.00',
        'description' => 'Car repair',
        'spent_on' => '2026-01-20',
        'bank_id' => $bankId,
        'recipient_id' => $recipientId,
    ]);

    $spend = FundSpend::query()->firstOrFail();
    expect($spend->status->value)->toBe('pending');

    $indexBeforeConfirm = $this->actingAs($user)->get(route('savings.spending.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $indexBeforeConfirm->assertInertia(fn (Assert $page) => $page
        ->where('fundBalances.0.spent', '0.00'),
    );

    $this->actingAs($user)->post(route('savings.spending.confirm', [
        'current_team' => $user->currentTeam->slug,
        'fundSpend' => $spend->id,
    ]));

    $indexAfterConfirm = $this->actingAs($user)->get(route('savings.spending.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $indexAfterConfirm->assertInertia(fn (Assert $page) => $page
        ->where('fundBalances.0.spent', '5000.00')
        ->where('fundBalances.0.remaining', '30000.00'),
    );
});

it('records spending with an optional receipt image', function () {
    [$user, , $everydayCategory] = createUserWithLockedIncome();

    Storage::fake('public');

    $response = $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '250.00',
        'description' => 'Coffee shop',
        'spent_on' => '2026-01-16',
        'receipt_image' => UploadedFile::fake()->image('receipt.jpg'),
    ]);

    $response->assertRedirect();

    $spend = FundSpend::query()->firstOrFail();
    expect($spend->receipt_image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($spend->receipt_image_path);

    $indexResponse = $this->actingAs($user)->get(route('savings.spending.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $indexResponse->assertInertia(fn (Assert $page) => $page
        ->where('spends.0.description', 'Coffee shop')
        ->where('spends.0.receiptImageUrl', fn ($url) => is_string($url) && $url !== ''),
    );
});

it('shows transfers page', function () {
    [$user] = createUserWithLockedIncome();

    $response = $this->actingAs($user)->get(route('savings.transfers.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/transfers/index'),
    );
});

it('shows remaining balances on income detail when income is locked', function () {
    [$user, , $everydayCategory, $period] = createUserWithLockedIncome();

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '1000.00',
        'description' => 'Transport',
        'spent_on' => '2026-01-10',
    ]);

    $response = $this->actingAs($user)->get(route('savings.income.show', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('fundBalances', 3)
        ->where('fundBalances.0.remaining', '34000.00'),
    );
});

it('blocks unlocking income when spending exceeds remaining allocation', function () {
    [$user, , $everydayCategory, $period] = createUserWithLockedIncome();

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '10000.00',
        'description' => 'Rent',
        'spent_on' => '2026-01-05',
    ]);

    $response = $this->actingAs($user)->post(route('savings.income.unlock', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertSessionHasErrors('period');
});

it('includes fund health on reports page', function () {
    [$user, , $everydayCategory] = createUserWithLockedIncome();

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '2500.00',
        'description' => 'Utilities',
        'spent_on' => '2026-01-12',
    ]);

    $response = $this->actingAs($user)->get(route('savings.reports', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('totals.fund_health', 3)
        ->where('totals.fund_health.0.category_name', 'Everyday Fund')
        ->where('totals.fund_health.0.spent', '2500.00')
        ->where('totals.fund_health.0.remaining', '32500.00'),
    );
});
