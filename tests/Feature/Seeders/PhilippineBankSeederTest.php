<?php

use App\Enums\BankInstitutionType;
use App\Models\BankInstitution;
use Database\Seeders\PhilippineBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('seeds philippine bank institutions idempotently', function () {
    Http::fake([
        '*' => Http::response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
            200,
            ['Content-Type' => 'image/png'],
        ),
    ]);

    $this->seed(PhilippineBankSeeder::class);
    $firstCount = BankInstitution::query()->count();

    $this->seed(PhilippineBankSeeder::class);

    expect(BankInstitution::query()->count())->toBe($firstCount)
        ->and($firstCount)->toBeGreaterThan(0);
});

it('seeds known banks and e-wallets with correct types', function () {
    Http::fake([
        '*' => Http::response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
            200,
            ['Content-Type' => 'image/png'],
        ),
    ]);

    $this->seed(PhilippineBankSeeder::class);

    $bdo = BankInstitution::query()->where('slug', 'bdo')->first();
    $gcash = BankInstitution::query()->where('slug', 'gcash')->first();
    $maya = BankInstitution::query()->where('slug', 'maya')->first();

    expect($bdo)->not->toBeNull()
        ->and($bdo->type)->toBe(BankInstitutionType::Bank)
        ->and($gcash)->not->toBeNull()
        ->and($gcash->type)->toBe(BankInstitutionType::EWallet)
        ->and($maya)->not->toBeNull()
        ->and($maya->type)->toBe(BankInstitutionType::EWallet)
        ->and(BankInstitution::query()->where('type', BankInstitutionType::Bank)->exists())->toBeTrue()
        ->and(BankInstitution::query()->where('type', BankInstitutionType::EWallet)->exists())->toBeTrue();
});

it('seeds gotyme with savings space features', function () {
    Http::fake([
        '*' => Http::response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
            200,
            ['Content-Type' => 'image/png'],
        ),
    ]);

    $this->seed(PhilippineBankSeeder::class);

    $gotyme = BankInstitution::query()->where('slug', 'gotyme')->firstOrFail();

    expect($gotyme->supportsSavingsSpaces())->toBeTrue()
        ->and($gotyme->maxSavingsSpaces())->toBe(5)
        ->and($gotyme->savingsSpacesConfig())->toMatchArray([
            'max' => 5,
            'main_label' => 'Main account',
            'space_label_prefix' => 'GoSave',
        ]);
});

it('stores logos on the public disk when download succeeds', function () {
    Http::fake([
        '*' => Http::response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
            200,
            ['Content-Type' => 'image/png'],
        ),
    ]);

    $this->seed(PhilippineBankSeeder::class);

    $gcash = BankInstitution::query()->where('slug', 'gcash')->firstOrFail();

    expect($gcash->logo_path)->not->toBeNull()
        ->and(Storage::disk('public')->exists($gcash->logo_path))->toBeTrue()
        ->and($gcash->logo_url)->toContain('storage/'.$gcash->logo_path);
});

it('does not download logos that already exist on disk', function () {
    Http::fake([
        '*' => Http::response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
            200,
            ['Content-Type' => 'image/png'],
        ),
    ]);

    /** @var list<array{slug: string}> $institutions */
    $institutions = require database_path('data/philippine-bank-institutions.php');

    foreach ($institutions as $row) {
        Storage::disk('public')->put("bank-institutions/{$row['slug']}.png", 'existing-logo');
    }

    $this->seed(PhilippineBankSeeder::class);

    Http::assertNothingSent();
});

it('continues seeding when logo download fails', function () {
    Http::fake([
        '*' => Http::response('Not found', 404),
    ]);

    $this->seed(PhilippineBankSeeder::class);

    $bdo = BankInstitution::query()->where('slug', 'bdo')->first();

    expect($bdo)->not->toBeNull()
        ->and($bdo->logo_path)->toBeNull();
});
