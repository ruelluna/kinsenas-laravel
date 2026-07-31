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

    test()->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'BPI',
    ]);

    $bank = Bank::query()->firstOrFail();

    return [$user, $plan, $everydayCategory, $bank];
}

it('shows transfers page with fund balances including transferred column', function () {
    [$user, , $everydayCategory] = createTransferFixture();

    $response = $this->actingAs($user)->get(route('savings.transfers.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/transfers/index')
        ->where('defaultCategoryId', $everydayCategory->id)
        ->where('fundBalances.0.transferred', '0.00')
        ->where('fundBalances.0.remaining', '35000.00'),
    );
});

it('records a pending transfer and confirms it', function () {
    [$user, , $everydayCategory, $bank] = createTransferFixture();

    $this->actingAs($user)->post(route('savings.transfers.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'bank_id' => $bank->id,
        'amount' => '5000.00',
        'description' => 'Payroll allocation',
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
    [$user, , $everydayCategory, $bank] = createTransferFixture();

    $this->actingAs($user)->post(route('savings.transfers.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'bank_id' => $bank->id,
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

it('rejects transfer to bank not assigned to category when assignments exist', function () {
    [$user, $plan, $everydayCategory, $bank] = createTransferFixture();

    $otherBank = Bank::factory()->create([
        'team_id' => $user->currentTeam->id,
        'name' => 'GCash',
    ]);

    $everydayCategory->banks()->sync([$bank->id]);

    $response = $this->actingAs($user)->post(route('savings.transfers.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'bank_id' => $otherBank->id,
        'amount' => '1000.00',
        'description' => 'Wrong bank',
        'transferred_on' => '2026-01-15',
    ]);

    $response->assertSessionHasErrors('bank_id');
    expect(FundTransfer::query()->count())->toBe(0);
});

it('calculates bank balance as transfers minus spending', function () {
    [$user, , $everydayCategory, $bank] = createTransferFixture();

    $this->actingAs($user)->post(route('savings.transfers.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'bank_id' => $bank->id,
        'amount' => '8000.00',
        'description' => 'Deposit',
        'transferred_on' => '2026-01-10',
    ]);

    $transfer = FundTransfer::query()->firstOrFail();

    $this->actingAs($user)->post(route('savings.transfers.confirm', [
        'current_team' => $user->currentTeam->slug,
        'fundTransfer' => $transfer->id,
    ]));

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '1500.00',
        'description' => 'Groceries',
        'spent_on' => '2026-01-12',
        'bank_id' => $bank->id,
    ]);

    $spend = \App\Models\FundSpend::query()->firstOrFail();

    $this->actingAs($user)->post(route('savings.spending.confirm', [
        'current_team' => $user->currentTeam->slug,
        'fundSpend' => $spend->id,
    ]));

    $response = $this->actingAs($user)->get(route('savings.banks.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('bankBalances.0.total', '6500.00')
        ->where('bankBalances.0.byCategory.0.total', '6500.00'),
    );
});
