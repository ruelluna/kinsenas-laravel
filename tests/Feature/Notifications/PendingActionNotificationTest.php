<?php

use App\Enums\TeamRole;
use App\Enums\TransferStatus;
use App\Models\Bank;
use App\Models\FundSpend;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Notifications\Savings\PendingSpendConfirmation;
use App\Services\Notifications\PendingActionNotificationService;
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

it('notifies team members when a pending spend is created', function () {
    Notification::fake();

    [$creator] = createUserWithLockedIncome();
    $teammate = User::factory()->create();
    $team = $creator->currentTeam;
    $team->members()->attach($teammate, ['role' => TeamRole::Member->value]);

    $plan = SavingsPlan::query()->firstOrFail();
    $category = $plan->categories()->firstOrFail();
    $bank = Bank::factory()->create(['team_id' => $team->id]);

    $spend = FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $category->id,
        'bank_id' => $bank->id,
        'status' => TransferStatus::Pending,
    ]);

    app(PendingActionNotificationService::class)->notifyForSpend($spend, $creator);

    Notification::assertSentTo($teammate, PendingSpendConfirmation::class);
    Notification::assertNotSentTo($creator, PendingSpendConfirmation::class);
});

it('deduplicates pending spend notifications by spend id', function () {
    [$creator] = createUserWithLockedIncome();
    $teammate = User::factory()->create();
    $team = $creator->currentTeam;
    $team->members()->attach($teammate, ['role' => TeamRole::Member->value]);

    $plan = SavingsPlan::query()->firstOrFail();
    $category = $plan->categories()->firstOrFail();
    $bank = Bank::factory()->create(['team_id' => $team->id]);

    $spend = FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $category->id,
        'bank_id' => $bank->id,
        'status' => TransferStatus::Pending,
    ]);

    $service = app(PendingActionNotificationService::class);
    $service->notifyForSpend($spend, $creator);
    $service->notifyForSpend($spend, $creator);

    expect($teammate->notifications()->count())->toBe(1);
});
