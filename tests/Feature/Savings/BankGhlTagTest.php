<?php

use App\Models\Bank;
use App\Models\BankInstitution;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\PhilippineBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    Http::fake(function (Request $request) {
        $url = $request->url();

        if (str_contains($url, '/contacts/upsert')) {
            return Http::response(['contact' => ['id' => 'ct_test_123']], 200);
        }

        if (preg_match('#/contacts/[^/]+/tags$#', $url) === 1) {
            return Http::response(['ok' => true], 200);
        }

        return Http::response(['ok' => true], 200);
    });

    config([
        'services.ghl.enabled' => true,
        'services.ghl.pit' => 'test-pit-token',
        'services.ghl.location_id' => 'loc_test_123',
    ]);

    $this->seed([
        BillingSeeder::class,
        PhilippineBankSeeder::class,
    ]);
});

it('adds institution and bank-added tags when a BDO bank is created', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $bdo = BankInstitution::query()->where('slug', 'bdo')->firstOrFail();

    $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $bdo->id,
    ])->assertRedirect();

    Http::assertSent(function ($request) {
        $tags = collect($request->data()['tags'] ?? []);

        return $request->method() === 'POST'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('bank-added')
            && $tags->contains('bdo-bank-added');
    });
});

it('removes institution tag when the last bank of that institution is deleted', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $bdo = BankInstitution::query()->where('slug', 'bdo')->firstOrFail();

    $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $bdo->id,
    ])->assertRedirect();

    $bank = Bank::query()->where('team_id', $user->currentTeam->id)->firstOrFail();

    $this->actingAs($user)->delete(route('savings.banks.destroy', [
        'current_team' => $user->currentTeam->slug,
        'bank' => $bank->id,
    ]))->assertRedirect();

    Http::assertSent(function ($request) {
        $tags = collect($request->data()['tags'] ?? []);

        return $request->method() === 'DELETE'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('bdo-bank-added')
            && $tags->contains('bank-added');
    });
});

it('adds gotyme gosave setup tag when savings spaces are created', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $gotyme = BankInstitution::query()->where('slug', 'gotyme')->firstOrFail();

    $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'bank_institution_id' => $gotyme->id,
        'main_label' => 'Main account',
        'spaces' => [
            ['label' => 'Vacation', 'enabled' => true],
        ],
    ])->assertRedirect();

    Http::assertSent(function ($request) {
        $tags = collect($request->data()['tags'] ?? []);

        return $request->method() === 'POST'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('gotyme-bank-added')
            && $tags->contains('gotyme-gosave-setup');
    });
});
