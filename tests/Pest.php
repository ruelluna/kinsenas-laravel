<?php

use App\Enums\SubscriptionStatus;
use App\Models\IncomePeriod;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Fortify\Features;
use Tests\TestCase;

uses(TestCase::class)->in('Feature');
uses(TestCase::class)->in('Unit');
uses(TestCase::class)->in('Browser');

/*
|--------------------------------------------------------------------------
| Browser assets
|--------------------------------------------------------------------------
|
| Browser tests always use the Vite build manifest. A stale or IPv6-only
| public/hot file (common when `npm run dev` wrote [::1]) leaves Inertia
| pages as an empty #app shell under Playwright.
|
| Run `npm run build` before browser tests when frontend assets change.
|
*/
uses()->beforeEach(function (): void {
    app(Vite::class)
        ->useHotFile(base_path('tests/.vite-hot-disabled'));
})->in('Browser');

/*
|--------------------------------------------------------------------------
| GHL safety net
|--------------------------------------------------------------------------
|
| phpunit.xml force-disables GHL credentials from .env. Tests that assert
| sync should call fakeGhlApi() to opt in with a mocked client.
|
| Do not register a global Http fake for leadconnectorhq.com here — Laravel
| merges fake callbacks, so a global stub would win over fakeGhlApi() and
| return {ok: true} without contact.id (skipping tag add/remove calls).
|
*/

function skipUnlessFortifyHas(string $feature, ?string $message = null): void
{
    if (! Features::enabled($feature)) {
        test()->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
    }
}

function grantTeamSubscriptionAccess(Team $team): Subscription
{
    $plan = SubscriptionPlan::query()->firstOrFail();

    return Subscription::query()->updateOrCreate(
        ['team_id' => $team->id],
        [
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'trial_ends_at' => null,
            'current_period_ends_at' => now()->addMonth(),
        ],
    );
}

function fakeGhlApi(string $contactId = 'ct_test_123'): void
{
    config([
        'services.ghl.enabled' => true,
        'services.ghl.pit' => 'test-pit-token',
        'services.ghl.location_id' => 'loc_test_123',
        'services.ghl.base_url' => 'https://services.leadconnectorhq.com',
        'services.ghl.api_version' => '2021-07-28',
    ]);

    Http::fake(function (Request $request) use ($contactId) {
        $url = $request->url();

        if (str_contains($url, '/contacts/upsert')) {
            return Http::response(['contact' => ['id' => $contactId]], 200);
        }

        if (preg_match('#/contacts/[^/]+/tags$#', $url) === 1) {
            return Http::response(['ok' => true], 200);
        }

        return Http::response(['ok' => true], 200);
    });
}

function createUserWithPlan(string $templateSlug = 'abundant-formula'): User
{
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', $templateSlug)->firstOrFail();

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    return $user;
}

function createUserWithLockedIncome(string $amount = '50000.00'): array
{
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    test()->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'January salary',
        'amount' => $amount,
        'period_start' => '2026-01-01',
    ]);

    $period = IncomePeriod::query()->firstOrFail();

    $plan = SavingsPlan::query()->firstOrFail();
    $everydayCategory = $plan->categories()->where('name', 'Everyday Fund')->firstOrFail();

    return [$user, $plan, $everydayCategory, $period];
}

function browserUnlockVaultIfNeeded(mixed $page): mixed
{
    if (str_contains($page->url(), '/vault/unlock')) {
        $page->fill('password', 'password')
            ->press('Unlock');
    }

    return $page;
}

function browserDismissOnboardingTour(mixed $page, string $teamId): mixed
{
    $teamIdJson = json_encode($teamId);

    $page->script("(function () {
        const teamId = {$teamIdJson};

        try {
            localStorage.setItem(
                'kinsenas.onboardingTour.v1.' + teamId,
                JSON.stringify({ completedAt: new Date().toISOString() }),
            );
            sessionStorage.removeItem('kinsenas.onboardingTour.active.v1');
            sessionStorage.removeItem('kinsenas.onboardingTour.autoStart.v1');
        } catch (e) {}

        document.querySelector('.driver-popover-close-btn')?.click();
        document.querySelectorAll('.driver-overlay, .driver-popover').forEach((element) => {
            element.remove();
        });
        document.body.classList.remove('driver-active', 'driver-no-scroll', 'driver-fade');
    })();");

    return $page;
}

function browserLogin(mixed $page, $user): mixed
{
    $page->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('@login-button');

    browserUnlockVaultIfNeeded($page);

    if (str_contains($page->url(), '/dashboard')) {
        browserDismissOnboardingTour($page, $user->currentTeam->id);
    }

    return $page;
}
