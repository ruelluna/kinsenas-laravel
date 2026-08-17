<?php

namespace Database\Seeders;

use App\Enums\SubscriptionStatus;
use App\Enums\TransferStatus;
use App\Models\Bank;
use App\Models\IncomePeriod;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Team;
use App\Models\User;
use App\Services\Savings\FundSpendService;
use App\Services\Savings\FundTransferService;
use App\Services\Savings\IncomeCalculationService;
use App\Services\Savings\SavingsPlanService;
use App\Services\Users\FinanceActivityScoreService;
use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;
use Database\Seeders\Support\DemoAccountHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoAccountSeeder extends Seeder
{
    private const DEMO_EMAIL = 'demo@kinsenas.test';

    private const INCOME_PERIOD_TARGET = 6;

    public function run(): void
    {
        $this->call([
            SavingsFormulaTemplateSeeder::class,
            BillingSeeder::class,
            PhilippineBankSeeder::class,
        ]);

        $user = $this->resolveDemoUser();
        $team = $user->currentTeam;

        $this->ensureActiveSubscription($team);
        $this->unlockVault($user);

        $plan = $this->resolvePlan($user);

        if ($this->historyAlreadySeeded($plan)) {
            return;
        }

        $this->assignBanksToCategories($plan, $team);
        $this->seedHistory($plan, $user);

        app(FinanceActivityScoreService::class)->refreshTeam($team);
        app(FinanceActivityScoreService::class)->refreshUser($user);
    }

    private function resolveDemoUser(): User
    {
        $user = User::query()->where('email', self::DEMO_EMAIL)->first();

        if ($user !== null) {
            return $user;
        }

        return User::factory()->create([
            'name' => 'Demo Member',
            'email' => self::DEMO_EMAIL,
            'email_verified_at' => now(),
        ]);
    }

    private function ensureActiveSubscription(Team $team): void
    {
        $plan = SubscriptionPlan::query()->firstOrFail();

        Subscription::query()->updateOrCreate(
            ['team_id' => $team->id],
            [
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::Active,
                'trial_ends_at' => null,
                'current_period_ends_at' => now()->addMonth(),
            ],
        );
    }

    private function unlockVault(User $user, string $password = 'password'): void
    {
        $vault = $user->vault;

        if ($vault === null) {
            app(FinancialEncryptionService::class)->createUserVault($user, $password);
            $vault = $user->fresh()->vault;
        }

        $dek = app(FinancialEncryptionService::class)->unlockWithPassword($vault, $password);
        app(VaultKeyManager::class)->storeUserDek($dek);
    }

    private function resolvePlan(User $user): SavingsPlan
    {
        $planService = app(SavingsPlanService::class);
        $existingPlan = $planService->forTeam($user->currentTeam, $user);

        if ($existingPlan !== null) {
            return $existingPlan->load('categories');
        }

        $template = SavingsFormulaTemplate::query()
            ->where('slug', 'trc-savings')
            ->firstOrFail();

        return $planService->cloneFromTemplate(
            $user->currentTeam,
            $user,
            $template,
            $template->name,
        )->load('categories');
    }

    private function historyAlreadySeeded(SavingsPlan $plan): bool
    {
        return IncomePeriod::query()
            ->where('plan_id', $plan->id)
            ->count() >= self::INCOME_PERIOD_TARGET;
    }

    private function assignBanksToCategories(SavingsPlan $plan, Team $team): void
    {
        $payrollBank = Bank::factory()
            ->mainSpace()
            ->create([
                'team_id' => $team->id,
                'name' => 'BDO',
                'account_label' => 'Payroll',
                'sort_order' => 0,
            ]);

        $goSaveBank = Bank::factory()
            ->savingsSpace('Emergency & Savings')
            ->create([
                'team_id' => $team->id,
                'name' => 'BDO',
                'account_label' => 'GoSave',
                'sort_order' => 1,
            ]);

        $categoriesByName = $plan->categories->keyBy('name');
        $assignments = DemoAccountHistory::categoryBankAssignments();

        foreach ($assignments['payroll'] as $categoryName) {
            $categoriesByName->get($categoryName)?->update(['bank_id' => $payrollBank->id]);
        }

        foreach ($assignments['gosave'] as $categoryName) {
            $categoriesByName->get($categoryName)?->update(['bank_id' => $goSaveBank->id]);
        }

        $plan->load('categories');
    }

    private function seedHistory(SavingsPlan $plan, User $user): void
    {
        $incomeService = app(IncomeCalculationService::class);
        $spendService = app(FundSpendService::class);
        $transferService = app(FundTransferService::class);

        $categoriesByName = $plan->categories->keyBy('name');

        foreach (DemoAccountHistory::months() as $month) {
            $periodStart = $month['income']['period_start'];
            $historicalAt = Carbon::parse($periodStart)->startOfDay();

            $period = $incomeService->create(
                $plan,
                $user,
                $month['income']['name'],
                $month['income']['amount'],
                $periodStart,
            );

            $period->update([
                'locked_at' => $historicalAt,
                'created_at' => $historicalAt,
                'updated_at' => $historicalAt,
            ]);

            foreach ($month['spends'] as $spend) {
                $category = $categoriesByName->get($spend['category']);

                if ($category === null) {
                    continue;
                }

                $spentOn = $this->dateOnDay($periodStart, $spend['day']);

                $fundSpend = $spendService->create(
                    $plan,
                    $category->id,
                    $spend['amount'],
                    $spend['description'],
                    $spentOn,
                    bankId: null,
                    user: $user,
                );

                $fundSpend->update([
                    'created_at' => Carbon::parse($spentOn)->startOfDay(),
                    'updated_at' => Carbon::parse($spentOn)->startOfDay(),
                ]);
            }

            foreach ($month['transfers'] as $transfer) {
                $fromCategory = $categoriesByName->get($transfer['from']);
                $toCategory = $categoriesByName->get($transfer['to']);

                if ($fromCategory === null || $toCategory === null) {
                    continue;
                }

                $transferredOn = $this->dateOnDay($periodStart, $transfer['day']);

                $fundTransfer = $transferService->create(
                    $plan,
                    $fromCategory->id,
                    $toCategory->id,
                    $transfer['amount'],
                    $transfer['description'],
                    $transferredOn,
                    $user,
                );

                if ($fundTransfer->status === TransferStatus::Pending) {
                    $transferService->confirm($fundTransfer, $user);
                }

                $fundTransfer->refresh()->update([
                    'created_at' => Carbon::parse($transferredOn)->startOfDay(),
                    'updated_at' => Carbon::parse($transferredOn)->startOfDay(),
                ]);
            }
        }
    }

    private function dateOnDay(string $periodStart, int $day): string
    {
        return Carbon::parse($periodStart)->day($day)->toDateString();
    }
}
