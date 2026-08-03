# PWA install UX rollout

**Date:** 2026-08-04

## Summary

Completed the PWA install UX plan: Tailwind install banner with eligibility gates, iOS 3-step guide sheet, persistent install entry in More and Settings, `/launch` start URL, manifest polish, and feature tests.

## Changelog

- **Install banner** — shadcn Alert below header; dismiss via shared `kinsenas.dismiss.pwaInstall.v1` key
- **Eligibility** — subscribed members with vault unlocked; shows after 2nd app open or when user has a savings plan
- **iOS** — “How to install” opens bottom sheet with Share → Add to Home Screen → Add steps
- **Chromium** — `beforeinstallprompt` captured in `PwaInstallProvider`; Install app triggers native dialog
- **More menu** — Install app row re-opens guide or native prompt after banner dismiss
- **Settings → Appearance** — Install app section when install is available
- **`/launch`** — PWA `start_url`; guests → login, members → team dashboard, no team → teams settings
- **Manifest** — `id`, `categories`, `start_url: /launch`
- **Dev** — optional `VITE_PWA_DEV=true` enables SW in Vite dev

## Files touched

### Frontend

- `resources/js/lib/pwa-install.ts`
- `resources/js/lib/dismissible-banner.ts`
- `resources/js/contexts/pwa-install-context.tsx`
- `resources/js/components/pwa/*`
- `resources/js/layouts/app/app-sidebar-layout.tsx`
- `resources/js/components/mobile/mobile-more-sheet.tsx`
- `resources/js/pages/settings/appearance.tsx`
- `resources/js/pages/dashboard.tsx`

### Backend

- `app/Http/Controllers/PwaLaunchController.php`
- `routes/web.php`

### Config / build

- `vite.config.ts`
- `.env.example` (document `VITE_PWA_DEV` when added)

### Tests

- `tests/Feature/PwaLaunchTest.php`
- `tests/Feature/PwaManifestTest.php`

## Deploy / verify

```bash
npm run build
php artisan route:list --name=pwa.launch
```

After deploy, installed shortcuts should open `/launch` → dashboard.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/PwaLaunchTest.php tests/Feature/PwaManifestTest.php
npm run types:check
vendor/bin/pint --dirty
```

Re-run manifest test after `npm run build` to assert `start_url: /launch` (skipped until rebuild).

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run build` for install prompt on Chromium

### Happy path

1. Log in as beta member; visit app **twice** (or create a savings plan on dashboard)
2. Confirm **Install Kinsenas** banner below header (not above)
3. **Android Chrome** — tap Install app → native dialog
4. **iOS Safari** — tap How to install → 3-step sheet
5. Dismiss banner → open **More** → **Install app** still works
6. **Settings → Appearance** → Install app section
7. Install shortcut → opens dashboard (via `/launch`)

### Checks

- [ ] Banner does not show when vault locked or billing locked out
- [ ] Open beta + install banners stack cleanly (install below header, beta below install)
- [ ] Standalone mode hides all install UI

## Suggested application commit

```
Summary: Complete PWA install UX with launch route and iOS guide

Add eligibility-gated install banner, More/Settings install entry, iOS
install sheet, and /launch start URL for installed shortcuts. Manifest
gains id, categories, and start_url /launch.
```

## Implementation summary

- Install prompt is dismissible and rediscoverable from More and Appearance
- iOS users get a proper 3-step guide instead of a one-line hint
- PWA shortcuts land on dashboard when logged in, not the marketing welcome page
