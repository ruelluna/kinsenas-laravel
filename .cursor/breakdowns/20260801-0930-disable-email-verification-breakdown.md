# Disable email verification — breakdown

**Date:** 2026-08-01

## Summary

Email verification is disabled while no mail system is configured. New signups are marked verified immediately and can access beta pending, dashboard, settings, and admin routes without visiting a verification prompt.

## Changelog

- Removed Fortify `emailVerification` feature and `verified` route middleware
- Removed post-auth redirect to the verification notice page
- New users are auto-verified on registration (`markEmailAsVerified()`)
- Beta launch discount now grants on admin approval without a separate email-verification step
- Profile settings no longer show the “resend verification email” banner

## Files touched

### Backend

- `app/Models/User.php` — dropped `MustVerifyEmail` interface
- `config/fortify.php` — removed `Features::emailVerification()`
- `app/Actions/Fortify/CreateNewUser.php` — auto-verify on signup
- `app/Http/Responses/Concerns/RedirectsToCurrentTeam.php` — removed verification redirect
- `app/Services/Billing/BetaApplicationService.php` — discount on approval only
- `app/Providers/FortifyServiceProvider.php` — removed verify-email view/response binding
- `app/Http/Controllers/Settings/ProfileController.php` — `mustVerifyEmail` always false
- `routes/web.php`, `routes/settings.php` — removed `verified` middleware

### Tests

- Updated `tests/Feature/Auth/RegistrationTest.php`
- Updated `tests/Feature/OpenBeta/EmailVerificationGateTest.php`
- Updated `tests/Feature/OpenBeta/OpenBetaRegistrationTest.php`
- Deleted `tests/Feature/Auth/EmailVerificationTest.php`
- Deleted `tests/Feature/Auth/VerificationNotificationTest.php`

## Deploy steps

No migration. Deploy app code and clear config cache if used:

```bash
php artisan config:clear
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Auth/RegistrationTest.php
php artisan test --compact tests/Feature/OpenBeta/EmailVerificationGateTest.php
php artisan test --compact tests/Feature/OpenBeta/OpenBetaRegistrationTest.php
php artisan test --compact tests/Feature/Settings/ProfileUpdateTest.php

vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend props changed

### Happy path

1. Register a new account
2. Confirm you land on **beta pending** (open beta) or **dashboard** (live mode) — not verify-email
3. Log in as an existing unverified user — confirm no redirect to verify-email
4. Open **Settings → Profile** — confirm no “unverified email” banner

### Regression

- [ ] Login / logout still works
- [ ] Admin routes still require platform admin
- [ ] Beta pending / rejected gates still apply for non-admins

## Suggested application commit

```
Summary: Disable email verification until mail is configured

New signups are marked verified immediately and routes no longer require
the verified middleware, so beta users can register and use the app without
an email confirmation step.
```

## Implementation summary

- Signup no longer sends users to verify-email; they go straight to beta pending or dashboard
- Registration auto-sets `email_verified_at`
- Admin approval grants launch discount without waiting for email verification
