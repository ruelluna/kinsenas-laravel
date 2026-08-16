# Fix failing browser and welcome tests

**Date:** 2026-08-13 04:29

## Summary

Browser suite failures came from a stale Vite `public/hot` file (empty Inertia shell), PWA precache URLs missing the `/build/` prefix (404s on Pest’s HTTP server), and spending list `data-test` hooks duplicated on a hidden mobile row. Welcome Feature tests were asserting SSR HTML that PHPUnit never renders. Fixed assets/PWA/selectors and aligned Feature welcome coverage with Inertia props.

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Welcome Feature `assertSee` copy | Replace | Assert Inertia component + open-beta props; copy stays in Browser `WelcomeTest` |
| Spending list `data-test` | Desktop-only | Avoid Playwright matching `md:hidden` duplicate first |

## Changelog

- Browser tests ignore stale Vite hot file and use the build manifest
- PWA precache URLs prefixed with `/build/assets/`; skip SW registration on `127.0.0.1` / `localhost`
- Vite dev server prefers `127.0.0.1` for Playwright compatibility
- Welcome Feature tests assert Inertia props; Browser welcome covers loop/security copy
- Spending reimbursement browser test seeds via services (no Amp-blocking HTTP setup)
- Expecting-payback checkbox submits via hidden field tied to React state
- Reimbursement badge / record-payback `data-test` only on desktop list rows

## Files touched

**Tests / Pest:** `tests/Pest.php`, `tests/Feature/WelcomePageTest.php`, `tests/Browser/WelcomeTest.php`, `tests/Browser/SpendingReimbursementTest.php`

**Frontend:** `vite.config.ts`, `resources/js/lib/register-pwa.ts`, `resources/js/components/savings/add-spending-modal.tsx`, `resources/js/pages/savings/spending/index.tsx`

## Deploy / verify

- `npm run build` (PWA sync to public root)
- No migrations

## Suggested tests (run manually)

```bash
# Feature tests
php artisan test --compact tests/Feature/WelcomePageTest.php

# Browser tests
php artisan test --compact tests/Browser/WelcomeTest.php
php artisan test --compact tests/Browser/LoginTest.php
php artisan test --compact tests/Browser/GoTymeBankTest.php
php artisan test --compact tests/Browser/SpendingReimbursementTest.php

# Lint / format
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run build` (or `npm run dev` with Vite on `127.0.0.1`)

### Happy path

1. Log in as a member with a plan and income
2. Open **Spending** → **Add spending**
3. Check **Expecting payback**, pick a recipient, submit
4. Confirm **Awaiting payback** badge and **Record payback** → **Paid back**

### Checks

- [ ] No console errors
- [ ] Desktop spending list badges/actions work
- [ ] Mobile spending list still shows payback UI (without relying on `data-test`)

## Suggested application commit

```
Summary: Fix browser suite assets, PWA precache, and spending test selectors

Stale Vite hot + wrong SW precache paths left Inertia shells empty or 404ing under Pest; welcome Feature tests now assert props, and spending reimbursement UI exposes desktop-only data-test hooks.
```
