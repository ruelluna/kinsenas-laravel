<?php

use App\Enums\RecipientType;
use App\Models\FundSpend;
use App\Models\Recipient;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('creates a recipient', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $response = $this->actingAs($user)->post(route('savings.recipients.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'type' => RecipientType::Person->value,
        'name' => '  Mechanic  ',
        'notes' => 'Monthly maintenance',
    ]);

    $response->assertRedirect();

    $recipient = Recipient::query()->firstOrFail();

    expect($recipient->name)->toBe('Mechanic')
        ->and($recipient->notes)->toBe('Monthly maintenance')
        ->and($recipient->type)->toBe(RecipientType::Person);
});

it('rejects a recipient with an empty name', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $response = $this->actingAs($user)->post(route('savings.recipients.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'type' => RecipientType::Person->value,
        'name' => '   ',
    ]);

    $response->assertSessionHasErrors('name');
    expect(Recipient::query()->count())->toBe(0);
});

it('updates a recipient', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $recipient = Recipient::query()->create([
        'team_id' => $user->currentTeam->id,
        'type' => RecipientType::Person,
        'name' => 'Mechanic',
        'notes' => null,
    ]);

    $response = $this->actingAs($user)->put(route('savings.recipients.update', [
        'current_team' => $user->currentTeam->slug,
        'recipient' => $recipient->id,
    ]), [
        'type' => RecipientType::Organization->value,
        'name' => 'Auto Shop',
        'notes' => 'Preferred vendor',
    ]);

    $response->assertRedirect();

    $recipient->refresh();

    expect($recipient->name)->toBe('Auto Shop')
        ->and($recipient->type)->toBe(RecipientType::Organization)
        ->and($recipient->notes)->toBe('Preferred vendor');
});

it('deletes a recipient and nulls linked spending', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $recipient = Recipient::query()->create([
        'team_id' => $user->currentTeam->id,
        'name' => 'Mechanic',
    ]);

    $spend = FundSpend::factory()->create([
        'recipient_id' => $recipient->id,
    ]);

    $response = $this->actingAs($user)->delete(route('savings.recipients.destroy', [
        'current_team' => $user->currentTeam->slug,
        'recipient' => $recipient->id,
    ]));

    $response->assertRedirect();
    expect(Recipient::query()->count())->toBe(0);

    $spend->refresh();
    expect($spend->recipient_id)->toBeNull();
});

it('forbids updating another teams recipient', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->unlockVaultFor($user);
    $this->unlockVaultFor($otherUser);

    $recipient = Recipient::query()->create([
        'team_id' => $otherUser->currentTeam->id,
        'type' => RecipientType::Person,
        'name' => 'Other team recipient',
        'notes' => null,
    ]);

    $response = $this->actingAs($user)->put(route('savings.recipients.update', [
        'current_team' => $user->currentTeam->slug,
        'recipient' => $recipient->id,
    ]), [
        'type' => RecipientType::Person->value,
        'name' => 'Hacked',
        'notes' => null,
    ]);

    $response->assertNotFound();
});
