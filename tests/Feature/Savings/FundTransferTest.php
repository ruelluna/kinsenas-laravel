<?php

use App\Models\Bank;
use App\Models\FundTransfer;
use App\Models\IncomePeriod;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
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

function createTransferFixture(string $amount = '50000.00'): array
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

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();
    $everydayCategory = $plan->categories->firstWhere('name', 'Everyday Fund');
    $savingsCategory = $plan->categories->firstWhere('name', 'Savings');

    test()->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'BPI',
    ]);

    $bank = Bank::query()->firstOrFail();

    return [$user, $plan, $everydayCategory, $savingsCategory, $bank];
}

it('shows transfers page with fund balances including received column', function () {
    [$user, , $everydayCategory] = createTransferFixture();

    $response = $this->actingAs($user)->get(route('savings.transfers.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/transfers/index')
        ->where('defaultCategoryId', $everydayCategory->id)
        ->where('categories.0.id', $everydayCategory->id)
        ->where('fundBalances.0.transferred', '0.00')
        ->where('fundBalances.0.received', '0.00')
        ->where('fundBalances.0.remaining', '35000.00'),
    );
});

it('auto-confirms same-bank category transfers and updates both fund balances', function () {
    [$user, , $everydayCategory, $savingsCategory, $bank] = createTransferFixture();

    $everydayCategory->update(['bank_id' => $bank->id]);
    $savingsCategory->update(['bank_id' => $bank->id]);

    $this->actingAs($user)->post(route('savings.transfers.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'from_category_id' => $everydayCategory->id,
        'to_category_id' => $savingsCategory->id,
        'amount' => '5000.00',
        'description' => 'Rebalance to savings',
        'transferred_on' => '2026-01-15',
    ]);

    $transfer = FundTransfer::query()->firstOrFail();
    expect($transfer->status->value)->toBe('confirmed');

    $response = $this->actingAs($user)->get(route('savings.transfers.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('transfers.0.status', 'confirmed')
    );

    $balances = $response->original->getData()['page']['props']['fundBalances'];
    $everydayBalance = collect($balances)->firstWhere('categoryId', $everydayCategory->id);
    $savingsBalance = collect($balances)->firstWhere('categoryId', $savingsCategory->id);

    expect($everydayBalance['transferred'])->toBe('5000.00')
        ->and($everydayBalance['remaining'])->toBe('30000.00')
        ->and($savingsBalance['received'])->toBe('5000.00')
        ->and($savingsBalance['remaining'])->toBe('15000.00');
});

it('records a pending cross-bank transfer and confirms it', function () {
    [$user, , $everydayCategory, $savingsCategory, $bank] = createTransferFixture();

    $everydayCategory->update(['bank_id' => $bank->id]);

    $otherBank = Bank::factory()->create([
        'team_id' => $user->currentTeam->id,
        'name' => 'GCash',
    ]);
    $savingsCategory->update(['bank_id' => $otherBank->id]);

    $this->actingAs($user)->post(route('savings.transfers.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'from_category_id' => $everydayCategory->id,
        'to_category_id' => $savingsCategory->id,
        'amount' => '5000.00',
        'description' => 'Move to GCash savings',
        'transferred_on' => '2026-01-15',
    ]);

    $transfer = FundTransfer::query()->firstOrFail();
    expect($transfer->status->value)->toBe('pending');

    $beforeConfirm = $this->actingAs($user)->get(route('savings.transfers.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $beforeConfirm->assertInertia(fn (Assert $page) => $page
        ->where('fundBalances.0.transferred', '0.00')
        ->where('fundBalances.0.remaining', '35000.00'),
    );

    $this->actingAs($user)->post(route('savings.transfers.confirm', [
        'current_team' => $user->currentTeam->slug,
        'fundTransfer' => $transfer->id,
    ]));

    $afterConfirm = $this->actingAs($user)->get(route('savings.transfers.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $afterConfirm->assertInertia(fn (Assert $page) => $page
        ->where('fundBalances.0.transferred', '5000.00')
        ->where('fundBalances.0.remaining', '30000.00'),
    );
});

it('rejects transfer above remaining balance on confirm', function () {
    [$user, , $everydayCategory, $savingsCategory, $bank] = createTransferFixture();

    $otherBank = Bank::factory()->create(['team_id' => $user->currentTeam->id, 'name' => 'GCash']);
    $everydayCategory->update(['bank_id' => $bank->id]);
    $savingsCategory->update(['bank_id' => $otherBank->id]);

    $this->actingAs($user)->post(route('savings.transfers.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'from_category_id' => $everydayCategory->id,
        'to_category_id' => $savingsCategory->id,
        'amount' => '40000.00',
        'description' => 'Too much',
        'transferred_on' => '2026-01-15',
    ]);

    $transfer = FundTransfer::query()->firstOrFail();

    $response = $this->actingAs($user)->post(route('savings.transfers.confirm', [
        'current_team' => $user->currentTeam->slug,
        'fundTransfer' => $transfer->id,
    ]));

    $response->assertSessionHasErrors('amount');
});

it('rejects transfer to the same category', function () {
    [$user, , $everydayCategory, , $bank] = createTransferFixture();

    $everydayCategory->update(['bank_id' => $bank->id]);

    $response = $this->actingAs($user)->post(route('savings.transfers.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'from_category_id' => $everydayCategory->id,
        'to_category_id' => $everydayCategory->id,
        'amount' => '1000.00',
        'description' => 'Same fund',
        'transferred_on' => '2026-01-15',
    ]);

    $response->assertSessionHasErrors('to_category_id');
    expect(FundTransfer::query()->count())->toBe(0);
});

it('shows zero bank category balances before any transfers', function () {
    [$user, , $everydayCategory, , $bank] = createTransferFixture();

    $everydayCategory->update(['bank_id' => $bank->id]);

    $response = $this->actingAs($user)->get(route('savings.banks.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('bankBalances.0.total', '0.00')
        ->where('bankBalances.0.byCategory.0.total', '0.00'),
    );
});

it('shifts bank balances for confirmed category transfers', function () {
    [$user, , $everydayCategory, $savingsCategory, $bank] = createTransferFixture();

    $everydayCategory->update(['bank_id' => $bank->id]);
    $savingsCategory->update(['bank_id' => $bank->id]);

    $this->actingAs($user)->post(route('savings.transfers.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'from_category_id' => $everydayCategory->id,
        'to_category_id' => $savingsCategory->id,
        'amount' => '8000.00',
        'description' => 'Rebalance',
        'transferred_on' => '2026-01-10',
    ]);

    $response = $this->actingAs($user)->get(route('savings.banks.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();

    $bankBalance = collect($response->original->getData()['page']['props']['bankBalances'])->first();
    $byCategory = collect($bankBalance['byCategory']);
    $everydayRow = $byCategory->firstWhere('categoryId', $everydayCategory->id);
    $savingsRow = $byCategory->firstWhere('categoryId', $savingsCategory->id);

    expect($bankBalance['total'])->toBe('0.00')
        ->and($everydayRow['total'])->toBe('-8000.00')
        ->and($savingsRow['total'])->toBe('8000.00');
});
