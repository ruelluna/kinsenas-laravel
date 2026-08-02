<?php

use App\Enums\SubscriptionStatus;
use App\Models\IncomePeriod;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Fortify\Features;
use Tests\TestCase;

uses(TestCase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

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

    test()->actingAs($user)->post(route('savings.income.lock', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $plan = SavingsPlan::query()->firstOrFail();
    $everydayCategory = $plan->categories()->where('name', 'Everyday Fund')->firstOrFail();

    return [$user, $plan, $everydayCategory, $period];
}
