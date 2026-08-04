# Fix mobile Driver.js onboarding tour

**Date:** 2026-08-05

## Summary

Split the onboarding tour into separate desktop and mobile Driver.js paths. Desktop keeps the original sidebar-based tour unchanged. Mobile adds `data-tour` anchors on the bottom nav and More sheet, auto-opens More before Banks/Plan spotlights, and falls back to page navigation when targets are missing.

## Changelog

- Tour dispatcher routes to `run-tour-desktop.ts` (≥768px) or `run-tour-mobile.ts` (<768px)
- Desktop tour logic extracted verbatim — sidebar highlights unchanged
- Mobile bottom nav and More sheet links now expose `data-tour` attributes
- Mobile tour opens More sheet before Banks / Savings Plan steps
- Mobile fallback navigates to Banks or Plan page if spotlight target is missing
- Income step uses popover above bottom tab bar on mobile
- Mobile resume delay increased to 500ms (desktop stays 350ms)
- Fixed PWA install banner hydration mismatch (`clientReady` gate in `pwa-install-context.tsx`)

## Files touched

### Tour modules

- `resources/js/lib/onboarding-tour/steps-shared.ts` (new)
- `resources/js/lib/onboarding-tour/steps-desktop.ts` (new)
- `resources/js/lib/onboarding-tour/steps-mobile.ts` (new)
- `resources/js/lib/onboarding-tour/steps.ts` (barrel)
- `resources/js/lib/onboarding-tour/is-mobile-viewport.ts` (new)
- `resources/js/lib/onboarding-tour/tour-driver-registry.ts` (new)
- `resources/js/lib/onboarding-tour/tour-lifecycle.ts` (new)
- `resources/js/lib/onboarding-tour/run-tour-desktop.ts` (new)
- `resources/js/lib/onboarding-tour/run-tour-mobile.ts` (new)
- `resources/js/lib/onboarding-tour/run-tour.ts` (dispatcher)
- `resources/js/lib/mobile-more-sheet-bridge.ts` (new)

### Mobile nav

- `resources/js/contexts/mobile-nav-context.tsx`
- `resources/js/components/mobile/mobile-bottom-nav.tsx`
- `resources/js/components/mobile/mobile-more-sheet.tsx`

### Host / CSS

- `resources/js/components/onboarding/onboarding-tour-host.tsx`
- `resources/css/app.css`

## Deploy / migration

None.

## Kinsenas verify checklist

- [ ] `npm run dev` or `npm run build` — frontend changed
- [ ] Manual check: http://financial-literacy.test

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/DashboardTest.php
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`

### Desktop regression (≥768px)

1. Log in → Dashboard → **Take a tour**
2. Step 2 highlights **sidebar Banks** (not bottom nav)
3. Full tour completes with sidebar highlights for Banks, Plan, Income

### Mobile happy path (~375px)

1. Dashboard → **Take a tour**
2. Setup checklist highlights
3. **Banks**: More sheet opens → Banks row spotlighted
4. Next → Banks page intro + Add bank
5. **Savings Plan**: More opens → Plan row spotlighted
6. Next → Plan page highlights
7. **Income**: bottom tab spotlighted (popover above tab bar)
8. **Done**

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Desktop tour unchanged at ≥768px
- [ ] Mobile More sheet opens automatically on Banks/Plan nav steps

## Suggested application commit

```
Summary: Fix mobile Driver.js onboarding tour with separate desktop path

Desktop tour logic is isolated in run-tour-desktop.ts unchanged. Mobile uses
bottom nav and More sheet anchors with auto-open More and page fallback when
spotlight targets are missing.
```

## Linear paste block

```
Title: Fix mobile Driver.js onboarding tour

Description:
Split onboarding tour into separate desktop and mobile Driver.js setups.
Desktop sidebar highlights are unchanged. Mobile tour wires data-tour to bottom
nav and More sheet, opens More before Banks/Plan steps, and falls back to page
navigation when targets are missing.

Comment / instructions:
Run npm run dev. Visual QA: full tour at 375px and ≥768px. Suggested:
php artisan test --compact tests/Feature/DashboardTest.php
```
