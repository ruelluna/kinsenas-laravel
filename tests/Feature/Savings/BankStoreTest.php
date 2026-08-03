<?php

use App\Models\Bank;
use App\Models\BankInstitution;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\PhilippineBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        BillingSeeder::class,
        PhilippineBankSeeder::class,
    ]);
});

it('creates a custom bank without an institution', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $response = $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => '  Rural Credit Union  ',
        'account_label' => 'Payroll',
    ]);

    $response->assertRedirect();

    $bank = Bank::query()->firstOrFail();

    expect($bank->name)->toBe('Rural Credit Union')
        ->and($bank->account_label)->toBe('Payroll')
        ->and($bank->bank_institution_id)->toBeNull()
        ->and($bank->displayLabel())->toBe('Rural Credit Union — Payroll');
});

it('rejects a custom bank with an empty name', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $response = $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => '   ',
    ]);

    $response->assertSessionHasErrors('name');
    expect(Bank::query()->count())->toBe(0);
});

it('creates an institution-linked bank', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $bdo = BankInstitution::query()->where('slug', 'bdo')->firstOrFail();

    $response = $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $bdo->id,
        'name' => $bdo->name,
        'account_label' => 'Savings',
    ]);

    $response->assertRedirect();

    $bank = Bank::query()->firstOrFail();

    expect($bank->bank_institution_id)->toBe($bdo->id)
        ->and($bank->name)->toBe('BDO')
        ->and($bank->account_label)->toBe('Savings');
});

it('updates a custom bank name and account label', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $bank = Bank::factory()->for($user->currentTeam)->create([
        'name' => 'Old Name',
        'account_label' => 'Main',
        'bank_institution_id' => null,
    ]);

    $response = $this->actingAs($user)->put(route('savings.banks.update', [
        'current_team' => $user->currentTeam->slug,
        'bank' => $bank->id,
    ]), [
        'name' => 'New Name',
        'account_label' => 'Savings',
    ]);

    $response->assertRedirect();

    $bank->refresh();

    expect($bank->name)->toBe('New Name')
        ->and($bank->account_label)->toBe('Savings');
});

it('updates an institution bank account label only', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $bdo = BankInstitution::query()->where('slug', 'bdo')->firstOrFail();

    $bank = Bank::factory()->for($user->currentTeam)->create([
        'name' => 'BDO',
        'account_label' => null,
        'bank_institution_id' => $bdo->id,
    ]);

    $response = $this->actingAs($user)->put(route('savings.banks.update', [
        'current_team' => $user->currentTeam->slug,
        'bank' => $bank->id,
    ]), [
        'name' => 'BDO',
        'account_label' => 'Everyday',
    ]);

    $response->assertRedirect();

    $bank->refresh();

    expect($bank->name)->toBe('BDO')
        ->and($bank->account_label)->toBe('Everyday')
        ->and($bank->bank_institution_id)->toBe($bdo->id);
});

it('deletes a bank from the team', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $bank = Bank::factory()->for($user->currentTeam)->create([
        'name' => 'Custom Bank',
        'bank_institution_id' => null,
    ]);

    $response = $this->actingAs($user)->delete(route('savings.banks.destroy', [
        'current_team' => $user->currentTeam->slug,
        'bank' => $bank->id,
    ]));

    $response->assertRedirect();
    expect(Bank::query()->count())->toBe(0);
});

it('forbids updating another teams bank', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->unlockVaultFor($user);
    $this->unlockVaultFor($otherUser);

    $bank = Bank::factory()->for($otherUser->currentTeam)->create([
        'name' => 'Other team bank',
        'bank_institution_id' => null,
    ]);

    $response = $this->actingAs($user)->put(route('savings.banks.update', [
        'current_team' => $user->currentTeam->slug,
        'bank' => $bank->id,
    ]), [
        'name' => 'Hacked',
        'account_label' => null,
    ]);

    $response->assertNotFound();
});
