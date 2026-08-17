<?php

namespace App\Providers;

use App\Contracts\Vault\VaultKeyStore;
use App\Listeners\ClearVaultOnLogout;
use App\Listeners\LogWebPushNotificationFailed;
use App\Models\Bank;
use App\Models\FundAddedEntry;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\IncomePeriod;
use App\Models\SavingsPlan;
use App\Models\UserVault;
use App\Observers\FinanceActivityScoreObserver;
use App\Services\Vault\SessionVaultKeyStore;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use NotificationChannels\WebPush\Events\NotificationFailed;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SessionVaultKeyStore::class);

        $this->app->bind(VaultKeyStore::class, function ($app) {
            return $app->make(SessionVaultKeyStore::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Logout::class, ClearVaultOnLogout::class);
        Event::listen(NotificationFailed::class, LogWebPushNotificationFailed::class);

        $financeActivityObserver = FinanceActivityScoreObserver::class;

        Bank::observe($financeActivityObserver);
        SavingsPlan::observe($financeActivityObserver);
        IncomePeriod::observe($financeActivityObserver);
        FundSpend::observe($financeActivityObserver);
        FundTransfer::observe($financeActivityObserver);
        FundAddedEntry::observe($financeActivityObserver);
        UserVault::observe($financeActivityObserver);

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
