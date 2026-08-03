<?php

use App\Notifications\Savings\IncomeReminder;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

it('reminds users on their payday when income is missing', function () {
    Notification::fake();

    $user = createUserWithPlan();
    $user->update(['payday_day_of_month' => now()->day]);

    $this->artisan('notifications:income-reminder')->assertSuccessful();

    Notification::assertSentTo($user, IncomeReminder::class);
});

it('skips income reminders when income is already logged this month', function () {
    Notification::fake();

    $user = createUserWithPlan();
    $user->update(['payday_day_of_month' => now()->day]);

    $this->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'August salary',
        'amount' => '50000.00',
        'period_start' => now()->startOfMonth()->toDateString(),
    ]);

    $this->artisan('notifications:income-reminder')->assertSuccessful();

    Notification::assertNotSentTo($user, IncomeReminder::class);
});
