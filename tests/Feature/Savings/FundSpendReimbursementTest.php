<?php

use App\Models\FundSpend;
use App\Models\FundSpendReimbursement;
use App\Models\Recipient;
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

function createRecipientForTeam($user, string $name = 'Ana'): Recipient
{
    test()->actingAs($user)->post(route('savings.recipients.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'type' => 'person',
        'name' => $name,
    ]);

    return Recipient::query()->where('name', $name)->firstOrFail();
}

it('creates spend expecting payback and reduces balance', function () {
    [$user, , $everydayCategory] = createUserWithLockedIncome();
    $payer = createRecipientForTeam($user);

    $response = $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '1000.00',
        'description' => 'Electric bill for roommate',
        'spent_on' => '2026-01-15',
        'expects_reimbursement' => true,
        'expected_from_recipient_id' => $payer->id,
    ]);

    $response->assertRedirect();

    $spend = FundSpend::query()->firstOrFail();
    expect($spend->expects_reimbursement)->toBeTrue()
        ->and($spend->expected_from_recipient_id)->toBe($payer->id);

    $indexResponse = $this->actingAs($user)->get(route('savings.spending.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $indexResponse->assertInertia(fn (Assert $page) => $page
        ->where('fundBalances.0.spent', '1000.00')
        ->where('fundBalances.0.remaining', '34000.00')
        ->where('fundBalances.0.awaitingReimbursement', '1000.00')
        ->where('spends.0.reimbursementStatus', 'awaiting')
        ->where('spends.0.remainingOwed', '1000.00'),
    );
});

it('records partial payback and restores balance partially', function () {
    [$user, , $everydayCategory] = createUserWithLockedIncome();
    $payer = createRecipientForTeam($user);

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '1000.00',
        'description' => 'Electric bill',
        'spent_on' => '2026-01-15',
        'expects_reimbursement' => true,
        'expected_from_recipient_id' => $payer->id,
    ]);

    $spend = FundSpend::query()->firstOrFail();

    $response = $this->actingAs($user)->post(route('savings.spending.reimbursements.store', [
        'current_team' => $user->currentTeam->slug,
        'fundSpend' => $spend->id,
    ]), [
        'amount' => '600.00',
        'received_on' => '2026-01-20',
    ]);

    $response->assertRedirect();
    expect(FundSpendReimbursement::query()->count())->toBe(1);

    $indexResponse = $this->actingAs($user)->get(route('savings.spending.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $indexResponse->assertInertia(fn (Assert $page) => $page
        ->where('fundBalances.0.remaining', '34600.00')
        ->where('fundBalances.0.awaitingReimbursement', '400.00')
        ->where('spends.0.reimbursementStatus', 'partial')
        ->where('spends.0.reimbursedAmount', '600.00')
        ->where('spends.0.remainingOwed', '400.00'),
    );
});

it('resolves spend when fully repaid', function () {
    [$user, , $everydayCategory] = createUserWithLockedIncome();
    $payer = createRecipientForTeam($user);

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '1000.00',
        'description' => 'Electric bill',
        'spent_on' => '2026-01-15',
        'expects_reimbursement' => true,
        'expected_from_recipient_id' => $payer->id,
    ]);

    $spend = FundSpend::query()->firstOrFail();

    $this->actingAs($user)->post(route('savings.spending.reimbursements.store', [
        'current_team' => $user->currentTeam->slug,
        'fundSpend' => $spend->id,
    ]), [
        'amount' => '600.00',
        'received_on' => '2026-01-20',
    ]);

    $this->actingAs($user)->post(route('savings.spending.reimbursements.store', [
        'current_team' => $user->currentTeam->slug,
        'fundSpend' => $spend->id,
    ]), [
        'amount' => '400.00',
        'received_on' => '2026-01-25',
    ]);

    $indexResponse = $this->actingAs($user)->get(route('savings.spending.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $indexResponse->assertInertia(fn (Assert $page) => $page
        ->where('fundBalances.0.remaining', '35000.00')
        ->where('fundBalances.0.awaitingReimbursement', '0.00')
        ->where('spends.0.reimbursementStatus', 'resolved')
        ->where('spends.0.remainingOwed', '0.00'),
    );
});

it('rejects over-payback', function () {
    [$user, , $everydayCategory] = createUserWithLockedIncome();
    $payer = createRecipientForTeam($user);

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '1000.00',
        'description' => 'Electric bill',
        'spent_on' => '2026-01-15',
        'expects_reimbursement' => true,
        'expected_from_recipient_id' => $payer->id,
    ]);

    $spend = FundSpend::query()->firstOrFail();

    $response = $this->actingAs($user)->post(route('savings.spending.reimbursements.store', [
        'current_team' => $user->currentTeam->slug,
        'fundSpend' => $spend->id,
    ]), [
        'amount' => '1500.00',
        'received_on' => '2026-01-20',
    ]);

    $response->assertSessionHasErrors('amount');
    expect(FundSpendReimbursement::query()->count())->toBe(0);
});

it('closes expectation and removes from awaiting count', function () {
    [$user, , $everydayCategory] = createUserWithLockedIncome();
    $payer = createRecipientForTeam($user);

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '1000.00',
        'description' => 'Electric bill',
        'spent_on' => '2026-01-15',
        'expects_reimbursement' => true,
        'expected_from_recipient_id' => $payer->id,
    ]);

    $spend = FundSpend::query()->firstOrFail();

    $this->actingAs($user)->post(route('savings.spending.reimbursements.close', [
        'current_team' => $user->currentTeam->slug,
        'fundSpend' => $spend->id,
    ]));

    $indexResponse = $this->actingAs($user)->get(route('savings.spending.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $indexResponse->assertInertia(fn (Assert $page) => $page
        ->where('fundBalances.0.remaining', '34000.00')
        ->where('fundBalances.0.awaitingReimbursement', '0.00')
        ->where('spends.0.reimbursementStatus', 'closed'),
    );
});

it('rejects disabling expecting payback after reimbursements exist', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();
    $plan->update(['allow_editing_spends' => true]);
    $payer = createRecipientForTeam($user);

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '1000.00',
        'description' => 'Electric bill',
        'spent_on' => '2026-01-15',
        'expects_reimbursement' => true,
        'expected_from_recipient_id' => $payer->id,
    ]);

    $spend = FundSpend::query()->firstOrFail();

    $this->actingAs($user)->post(route('savings.spending.reimbursements.store', [
        'current_team' => $user->currentTeam->slug,
        'fundSpend' => $spend->id,
    ]), [
        'amount' => '200.00',
        'received_on' => '2026-01-20',
    ]);

    $response = $this->actingAs($user)->put(route('savings.spending.update', [
        'current_team' => $user->currentTeam->slug,
        'fundSpend' => $spend->id,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '1000.00',
        'description' => 'Electric bill',
        'spent_on' => '2026-01-15',
        'expects_reimbursement' => false,
    ]);

    $response->assertSessionHasErrors('expects_reimbursement');
});

it('requires expected from recipient when expecting payback', function () {
    [$user, , $everydayCategory] = createUserWithLockedIncome();

    $response = $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '500.00',
        'description' => 'Bill',
        'spent_on' => '2026-01-15',
        'expects_reimbursement' => true,
    ]);

    $response->assertSessionHasErrors('expected_from_recipient_id');
});
