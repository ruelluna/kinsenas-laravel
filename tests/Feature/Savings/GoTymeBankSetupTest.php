<?php

use App\Enums\BankSpaceRole;
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

it('creates main account and enabled gosave spaces in one setup', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    $gotyme = gotymeInstitution();

    $response = $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $gotyme->id,
        'main_label' => 'Main account',
        'spaces' => [
            ['label' => 'Vacation', 'enabled' => true],
            ['label' => 'Emergency', 'enabled' => true],
            ['label' => 'GoSave 3', 'enabled' => false],
        ],
    ]);

    $response->assertRedirect();

    $banks = Bank::query()->where('team_id', $user->currentTeam->id)->orderBy('sort_order')->get();

    expect($banks)->toHaveCount(3)
        ->and($banks->pluck('bank_account_group_id')->unique())->toHaveCount(1)
        ->and($banks->first()->space_role)->toBe(BankSpaceRole::Main)
        ->and($banks->first()->account_label)->toBe('Main account')
        ->and($banks->skip(1)->pluck('space_role')->unique()->all())->toBe([BankSpaceRole::SavingsSpace])
        ->and($banks->pluck('account_label')->all())->toBe(['Main account', 'Vacation', 'Emergency']);
});

it('rejects more than five gosave spaces', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    $gotyme = gotymeInstitution();

    $spaces = [];

    for ($index = 1; $index <= 6; $index++) {
        $spaces[] = ['label' => "GoSave {$index}", 'enabled' => true];
    }

    $response = $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $gotyme->id,
        'main_label' => 'Main account',
        'spaces' => $spaces,
    ]);

    $response->assertSessionHasErrors('spaces');
});

it('rejects duplicate space labels within a setup', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    $gotyme = gotymeInstitution();

    $response = $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $gotyme->id,
        'main_label' => 'Main account',
        'spaces' => [
            ['label' => 'Vacation', 'enabled' => true],
            ['label' => 'Vacation', 'enabled' => true],
        ],
    ]);

    $response->assertSessionHasErrors('spaces');
});

it('exposes display names and grouping on the banks page', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    $gotyme = gotymeInstitution();

    $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $gotyme->id,
        'main_label' => 'Main account',
        'spaces' => [
            ['label' => 'Vacation', 'enabled' => true],
        ],
    ]);

    $response = $this->actingAs($user)->get(route('savings.banks.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('savings/banks/index')
            ->has('banks', 2)
            ->where('banks.0.displayName', 'GoTyme Bank — Main account')
            ->where('banks.1.displayName', 'GoTyme Bank — Vacation')
            ->where('banks.0.bankAccountGroupId', fn ($value) => $value !== null)
            ->where('banks.1.bankAccountGroupId', fn ($value) => $value !== null));
});

it('allows assigning different gosave spaces to different categories', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    $gotyme = gotymeInstitution();

    $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $gotyme->id,
        'main_label' => 'Main account',
        'spaces' => [
            ['label' => 'Vacation', 'enabled' => true],
            ['label' => 'Emergency', 'enabled' => true],
        ],
    ]);

    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();
    $vacationBank = Bank::query()->where('account_label', 'Vacation')->firstOrFail();
    $emergencyBank = Bank::query()->where('account_label', 'Emergency')->firstOrFail();

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
