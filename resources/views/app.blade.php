<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#1E8B75">
        <meta name="description" content="Sweldo with a plan — payday allocation planner for Filipino households.">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Kinsenas">
        <meta name="mobile-web-app-capable" content="yes">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const cookieAppearance = '{{ $appearance ?? "system" }}';
                const storedAppearance = localStorage.getItem('appearance');
                const appearance = storedAppearance || cookieAppearance;
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = appearance === 'dark' || (appearance === 'system' && prefersDark);

                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(98.5% 0.008 165);
            }

            html.dark {
                background-color: oklch(16.3% 0.031 165.5);
            }
        </style>

        @if (app()->runningUnitTests())
            {{-- Prevent Driver.js onboarding tour from blocking Pest browser tests (no Vite rebuild required). --}}
            <script>
                (function () {
                    const blockedKeys = new Set([
                        'kinsenas.onboardingTour.active.v1',
                        'kinsenas.onboardingTour.autoStart.v1',
                    ]);

                    const patchStorage = (storage) => {
                        const originalSetItem = storage.setItem.bind(storage);

                        storage.setItem = function (key, value) {
                            if (blockedKeys.has(key)) {
                                return;
                            }

                            if (typeof key === 'string' && key.startsWith('kinsenas.onboardingTour.v1.')) {
                                return;
                            }

                            return originalSetItem(key, value);
                        };
                    };

                    try {
                        patchStorage(window.localStorage);
                        patchStorage(window.sessionStorage);
                    } catch (e) {
                        // Ignore private mode / disabled storage.
                    }
                })();
            </script>
        @endif

        <link rel="icon" href="/kinsenas-square-logo.png" type="image/png">
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="apple-touch-icon" href="/icons/icon-180.png">

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
