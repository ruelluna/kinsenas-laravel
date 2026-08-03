# PWA + @kinsenas/ui combined rollout

**Date:** 2026-08-04

## Summary

Shipped installable PWA infrastructure on the existing Laravel + Inertia + React stack, combined with `@kinsenas/ui` mobile components. Mobile sidebar now closes after navigation, safe-area padding supports notched phones in standalone mode, spending list uses `ResponsiveDataView` on small screens, and dashboard gets a quick-add spending FAB preset to Everyday fund.

## Changelog

- Mobile sidebar auto-closes after Platform and Admin nav link taps
- Safe-area CSS utilities and standalone header padding on app header, survey shell, vault unlock
- `@kinsenas/ui`: `InstallAppBanner` (Android install prompt + iOS Share hint), `MobileActionFab`
- Spending index: compact card list via `ResponsiveDataView` on mobile
- Dashboard: mobile FAB opens add-spending modal with Everyday fund preset (`quickSpend` props)
- PWA: `vite-plugin-pwa`, web manifest, service worker (NetworkFirst navigations, precache build assets)
- Generated brand PNG icons under `public/icons/` and `public/kinsenas-square-logo.png`
- SW update toast via Sonner when a new build is available (production only)
- Blade: viewport-fit, theme-color, apple-mobile-web-app meta tags

## Files touched

### Mobile UX (Phase A)

- `resources/js/hooks/use-close-mobile-sidebar.ts`
- `resources/js/components/nav-main.tsx`
- `resources/js/components/admin/admin-sidebar-nav.tsx`
- `resources/css/app.css`
- `resources/js/components/app-sidebar-header.tsx`
- `resources/js/components/survey/survey-shell.tsx`
- `resources/js/pages/vault/unlock.tsx`
- `resources/js/pages/savings/spending/index.tsx`

### @kinsenas/ui

- `packages/ui/src/components/install-app-banner.tsx`
- `packages/ui/src/components/mobile-action-fab.tsx`
- `packages/ui/src/index.ts`

### PWA (Phase B–C)

- `vite.config.ts`
- `resources/views/app.blade.php`
- `resources/js/app.tsx`
- `resources/js/types/vite-env.d.ts`
- `resources/js/layouts/app/app-sidebar-layout.tsx`
- `scripts/generate-pwa-icons.php`
- `public/icons/*`
- `public/kinsenas-square-logo.png`
- `package.json`

### Dashboard quick-add

- `app/Services/Dashboard/DashboardSummaryService.php`
- `resources/js/types/dashboard.ts`
- `resources/js/pages/dashboard.tsx`

## Deploy / verify

```bash
npm install
npm run icons:pwa          # regenerate PNGs if icon.svg changes
npm run build
vendor/bin/pint --dirty    # if PHP changed
```

No migrations.

## Suggested tests (run manually)

```bash
# Feature tests (dashboard quickSpend prop)
php artisan test --compact tests/Feature/DashboardTest.php

# Lint / format
vendor/bin/pint --dirty
npm run types:check
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` or `npm run build` after pulling

### Happy path

1. Log in as a member with a plan and unlocked income
2. On mobile width (~390px), open sidebar → tap **Spending** → sidebar closes
3. On **Dashboard**, tap the floating **+** button → add-spending modal opens with Everyday fund selected
4. On **Spending**, confirm recent activity shows compact cards on mobile
5. Run `npm run build`, reload — dismiss or accept install banner (Chrome Android); on iOS Safari use Share → Add to Home Screen
6. Open installed PWA — header clears the notch; vault unlock form has bottom safe padding

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Install banner dismiss persists after reload
- [ ] SW update toast appears after redeploy (production build)
- [ ] Lighthouse PWA audit: installable

### Regression

- [ ] Login/logout still works
- [ ] Desktop sidebar unchanged
- [ ] Add spending from Spending page still works

## Suggested application commit

```
Summary: Add PWA shell and mobile polish with @kinsenas/ui

Combines installable PWA infrastructure (manifest, service worker, icons)
with mobile UX fixes and shared UI components. Dashboard quick-add FAB
opens spending modal preset to Everyday fund.
```

## Changelog (2026-08-04 follow-up)

- Transfers index: compact `ResponsiveDataView` list on mobile; desktop row layout unchanged
- Transfers header: flex-wrap for narrow widths (matches spending/income)

**Suggested test:**

```bash
php artisan test --compact tests/Feature/Savings/FundTransferTest.php
```
