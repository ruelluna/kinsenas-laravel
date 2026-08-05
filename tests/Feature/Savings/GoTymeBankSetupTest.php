<?php

use App\Models\Bank;
use App\Models\BankInstitution;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\PhilippineBankSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    Http::fake([
        '*' => Http::response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
            200,
            ['Content-Type' => 'image/png'],
        ),
    ]);

    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
        PhilippineBankSeeder::class,
    ]);
});

function gotymeInstitution(): BankInstitution
{
    return BankInstitution::query()->where('slug', 'gotyme')->firstOrFail();
}

it('creates a single gotyme main account with label', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    $gotyme = gotymeInstitution();

    $response = $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $gotyme->id,
        'account_label' => 'GoTyme/Main',
    ]);

    $response->assertRedirect();

    $bank = Bank::query()->where('team_id', $user->currentTeam->id)->sole();

    expect($bank->account_label)->toBe('GoTyme/Main')
        ->and($bank->bank_account_group_id)->toBeNull()
        ->and($bank->space_role)->toBeNull();
});

it('creates a single gosave account independently of a main account', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    $gotyme = gotymeInstitution();

    $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $gotyme->id,
        'account_label' => 'GoSave/Mom',
    ])->assertRedirect();

    expect(Bank::query()->where('team_id', $user->currentTeam->id)->count())->toBe(1)
        ->and(Bank::query()->first()->account_label)->toBe('GoSave/Mom');
});

it('allows adding multiple gosave accounts without a limit', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    $gotyme = gotymeInstitution();

    for ($index = 1; $index <= 6; $index++) {
        $this->actingAs($user)->post(route('savings.banks.store', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'bank_institution_id' => $gotyme->id,
            'account_label' => "GoSave/Person {$index}",
        ])->assertRedirect();
    }

    expect(Bank::query()->where('team_id', $user->currentTeam->id)->count())->toBe(6);
});

it('exposes display names on the banks page', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    $gotyme = gotymeInstitution();

    $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $gotyme->id,
        'account_label' => 'GoSave/Mom',
    ]);

    $response = $this->actingAs($user)->get(route('savings.banks.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('savings/banks/index')
            ->has('banks', 1)
            ->where('banks.0.displayName', 'GoTyme Bank — GoSave/Mom')
            ->where('banks.0.bankAccountGroupId', null));
});

it('allows assigning different gosave labels to different categories', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    $gotyme = gotymeInstitution();

    foreach (['GoSave/Vacation', 'GoSave/Emergency'] as $label) {
        $this->actingAs($user)->post(route('savings.banks.store', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'bank_institution_id' => $gotyme->id,
            'account_label' => $label,
        ]);
    }

    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();
    $vacationBank = Bank::query()->where('account_label', 'GoSave/Vacation')->firstOrFail();
    $emergencyBank = Bank::query()->where('account_label', 'GoSave/Emergency')->firstOrFail();

    $payload = $plan->categories->map(fn ($category) => [
        'id' => $category->id,
        'name' => $category->name,
        'allocation_type' => $category->allocation_type->value,
        'percentage' => $category->percentage !== null ? (string) $category->percentage : null,
        'deduction_mode' => $category->deduction_mode?->value,
        'deduction_value' => $category->deduction_value !== null ? (string) $category->deduction_value : null,
        'deduct_from_category_id' => $category->deduct_from_category_id,
        'bank_id' => null,
    ])->all();

    $payload[0]['bank_id'] = $vacationBank->id;
    $payload[1]['bank_id'] = $emergencyBank->id;

    $response = $this->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => $payload,
    ]);

    $response->assertRedirect();

    expect($plan->categories()->where('bank_id', $vacationBank->id)->count())->toBe(1)
        ->and($plan->categories()->where('bank_id', $emergencyBank->id)->count())->toBe(1);
});
