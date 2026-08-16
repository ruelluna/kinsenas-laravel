<?php

use App\Http\Middleware\BindVaultKeyStore;
use App\Http\Middleware\EnsureApiTeamScope;
use App\Http\Middleware\EnsureLearnMemberAccess;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsureSavingsPlan;
use App\Http\Middleware\EnsureSubscribedOrTrialing;
use App\Http\Middleware\EnsureSubscriptionFeature;
use App\Http\Middleware\EnsureVaultUnlocked;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetTeamUrlDefaults;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->alias([
            'vault.unlocked' => EnsureVaultUnlocked::class,
            'subscribed' => EnsureSubscribedOrTrialing::class,
            'subscribed.feature' => EnsureSubscriptionFeature::class,
            'savings.plan.required' => EnsureSavingsPlan::class,
            'platform.admin' => EnsurePlatformAdmin::class,
            'learn.member' => EnsureLearnMemberAccess::class,
            'api.team' => EnsureApiTeamScope::class,
        ]);

        $middleware->web(prepend: [
            BindVaultKeyStore::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SetTeamUrlDefaults::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
