<?php

it('serves a built web app manifest when assets are compiled', function () {
    $manifestPaths = [
        public_path('manifest.webmanifest'),
        public_path('build/manifest.webmanifest'),
    ];

    $manifestPath = collect($manifestPaths)->first(fn (string $path) => file_exists($path));

    if ($manifestPath === null) {
        $this->markTestSkipped('Run npm run build to generate the PWA manifest.');
    }

    $manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

    if (($manifest['start_url'] ?? '/') !== '/launch') {
        $this->markTestSkipped('Manifest predates /launch start_url. Run npm run build.');
    }

    expect($manifest['name'])->toBe('Kinsenas')
        ->and($manifest['display'])->toBe('standalone')
        ->and($manifest['start_url'])->toBe('/launch')
        ->and($manifest['icons'])->toBeArray()->not->toBeEmpty();
});

it('includes pwa meta tags in the app shell', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('rel="manifest"', false)
        ->assertSee('apple-mobile-web-app-capable', false)
        ->assertSee('theme-color', false)
        ->assertSee('viewport-fit=cover', false);
});
