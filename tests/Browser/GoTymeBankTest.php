<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\PhilippineBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake([
        '*' => Http::response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
            200,
            ['Content-Type' => 'image/png'],
        ),
    ]);

    $this->seed([
        BillingSeeder::class,
        PhilippineBankSeeder::class,
    ]);
});

it('adds a gosave account through the banks modal', function () {
    $member = User::factory()->create([
        'email' => 'gotyme-browser@kinsenas.test',
    ]);

    grantTeamSubscriptionAccess($member->currentTeam);

    $banksUrl = '/'.$member->currentTeam->slug.'/savings/banks';

    $page = visit('/login');

    browserLogin($page, $member);

    $page->navigate($banksUrl)
        ->assertSee('Banks')
        ->click('@add-bank')
        ->fill('#bank-institution-search', 'GoTyme')
        ->click('GoTyme Bank')
        ->click('@gotyme-account-type-gosave')
        ->fill('@gotyme-account-name', 'Mom')
        ->click('@add-bank-submit')
        ->assertSee('GoTyme Bank — GoSave/Mom')
        ->assertNoSmoke();
});
