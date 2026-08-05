# Mobile member full parity

**Date:** 2026-08-05

## Summary

Delivered full member-feature parity on the Expo mobile app: Register/Login auth via Sanctum API, web-matching bottom tab navigation (Home/Income/Spending/More), expanded `/api/v1` routes for savings CRUD and settings/teams/notifications, and mobile screens wired through `@kinsenas/api-client`.

## Changelog

- **Auth API:** `POST /api/v1/auth/register`, forgot/reset password, `GET /api/v1/auth/register-context`, `GET /api/v1/auth/bootstrap`
- **Auth mobile:** Register, forgot password, reset password, verify email, beta pending/rejected screens; login links; AuthGate respects vault unlock and beta gates
- **Bottom nav:** Fixed tab bar (Home, Income, Spending, More) + More sheet matching web member nav
- **Savings API:** Full write endpoints for plan, banks, income, spending, transfers, recipients, reports
- **Settings API:** Profile, password, notifications prefs, feedback, billing payment, teams, invitations, notification inbox
- **Mobile screens:** Savings CRUD (plan, banks, recipients, reports, income detail/create, spending/transfer create, confirm pending), settings stack, notifications inbox, billing pay
- **Shared packages:** Expanded `@kinsenas/shared` types; nested `@kinsenas/api-client` domains (`auth`, `savings`, `settings`, `teams`, `notifications`)

## Files touched

### Backend

- `routes/api.php`
- `app/Actions/Fortify/CreateNewUser.php`
- `app/Http/Controllers/Api/V1/Auth/*` (Register, ForgotPassword, ResetPassword, RegisterContext, Bootstrap)
- `app/Http/Controllers/Api/V1/Savings/*` (Plan, Bank, Recipient, Report + extended Income/Spending/Transfer)
- `app/Http/Controllers/Api/V1/Settings/*`, `Teams/*`, `Notifications/*`
- `tests/Feature/Api/V1/AuthTest.php`, `Savings/*`, `Settings/*`, `Notifications/*`

### Packages

- `packages/shared/src/types/*`
- `packages/api-client/src/client.ts`

### Mobile

- `apps/mobile/app/(auth)/*`, `app/(app)/(tabs)/*`, `app/(app)/savings/*`, `app/(app)/settings/*`, `app/(app)/billing/*`, `app/(app)/notifications/*`
- `apps/mobile/components/mobile-bottom-nav.tsx`, `mobile-more-sheet.tsx`, `mobile-shell.tsx`, `mobile-fab.tsx`, `settings-links.tsx`
- `apps/mobile/lib/member-nav.ts`, `auth-routing.ts`, `theme-storage.ts`, `lib/schemas/*`

## Deploy / verify

```bash
# Backend
php artisan migrate   # if fresh DB
vendor/bin/pint --dirty

# Mobile
cd apps/mobile && npm install
cd apps/mobile && npm run types:check
cd apps/mobile && npx expo start
```

Set `EXPO_PUBLIC_API_URL` (default `http://financial-literacy.test`; Android emulator use `http://10.0.2.2`).

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Api/V1/AuthTest.php
php artisan test --compact tests/Feature/Api/V1/Savings/
php artisan test --compact tests/Feature/Api/V1/Settings/SettingsApiTest.php
php artisan test --compact tests/Feature/Api/V1/Notifications/NotificationApiTest.php

cd apps/mobile && npm run types:check
cd apps/mobile && npx expo start
```

## Visual QA (manual)

**URL:** Expo dev client / Android emulator  
**Prereqs:** `cd apps/mobile && npm install && npx expo start`, Laravel Herd at `http://financial-literacy.test`

### Happy path

1. Open app → **Login** → tap **Create an account**
2. **Register** → save recovery key → verify email notice
3. **Login** → vault unlock (if locked) → **Home** tab dashboard
4. Bottom tabs: **Home**, **Income**, **Spending**, **More**
5. **More** → Banks, Plan, Transfers, Recipients, Reports, Settings
6. **Income** tab → FAB → create income period → open detail → complete distribution todo
7. **Spending** tab → add spend → confirm pending on list
8. **More** → **Transfers** → create transfer → confirm pending
9. **Settings** → Profile, Security, Appearance (dark mode), Notifications, Billing, Teams
10. **More** → Sign out

### Checks

- [ ] No redbox / Metro errors
- [ ] Register recovery key modal appears once
- [ ] Bottom tab active state matches current screen
- [ ] More sheet closes after navigation
- [ ] `formatMoney` shows `₱` prefix on amounts
- [ ] Beta-pending user sees gate screen after login

### Regression

- [ ] Login/logout still works
- [ ] Team switch in Settings → Teams updates dashboard data

## Suggested application commit

```
Summary: Add mobile member full parity with auth API and bottom nav

Expands Sanctum API for registration, savings writes, and settings. Rebuilds Expo app with bottom tabs, More sheet, and CRUD screens matching the web member experience.
```

## Linear paste block

```
Title: Mobile member full parity (Register, Login, all screens, bottom nav)

Description:
- Sanctum auth API: register, forgot/reset password, bootstrap for mobile routing
- Full savings + settings + teams + notifications API v1 endpoints
- Expo mobile: bottom tabs, More sheet, auth screens, savings CRUD, settings stack

Comment / instructions:
Run API tests under tests/Feature/Api/V1/. Mobile: cd apps/mobile && npm install && npx expo start. Visual QA: register → login → Home/Income/Spending/More → create income/spending/transfer → settings profile.
```
