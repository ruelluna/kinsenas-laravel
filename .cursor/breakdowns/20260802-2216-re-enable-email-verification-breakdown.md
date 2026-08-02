# Re-enable email verification (Resend) — breakdown

**Date:** 2026-08-02 22:16

## Summary

Email verification is required again now that Resend is available. New registrations stay unverified, receive a Fortify verification email, and must verify before beta pending / app access. Profile settings show the resend-verification banner when applicable.

## Changelog

- Users implement `MustVerifyEmail` again; `verified` middleware restored on app, settings, vault, beta pending/rejected, and admin routes
- Registration no longer auto-sets `email_verified_at`
- Post-auth redirect sends unverified users to **Email verification** before open-beta pending/rejected
- Profile `mustVerifyEmail` is true when the user implements `MustVerifyEmail`
- `.env.example` documents `MAIL_MAILER=resend` and `RESEND_API_KEY`

### 2026-08-02 follow-up (test fixes)

- Auth registration/verification tests pin `billing.mode` to `live` so local `BILLING_MODE=open_beta` does not break trial assertions
- Slug-collision registration test logs out between signups
- Open-beta verify → beta pending covered in `EmailVerificationTest`
- `phpunit.xml` force-sets `BILLING_MODE=live` so the suite does not inherit open-beta from `.env` (OpenBeta tests still override via `config()`)

## Files touched

### Backend
- `app/Models/User.php`
- `app/Actions/Fortify/CreateNewUser.php`
- `app/Http/Responses/Concerns/RedirectsToCurrentTeam.php`
- `app/Http/Controllers/Settings/ProfileController.php`
- `routes/web.php`
- `routes/settings.php`

### Config
- `.env.example`

### Tests
- `tests/Feature/OpenBeta/EmailVerificationGateTest.php`
- `tests/Feature/Auth/EmailVerificationTest.php` (restored)
- `tests/Feature/Auth/VerificationNotificationTest.php` (restored)
- `tests/Feature/Auth/RegistrationTest.php`

## Deploy / verify

1. Set `MAIL_MAILER=resend`, `RESEND_API_KEY`, and a Resend-verified `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME`
2. If `QUEUE_CONNECTION` is not `sync`, run a queue worker so verification mail sends
3. Existing users already auto-verified keep access; only new/unverified users hit the gate
4. `vendor/bin/pint --dirty` (already run)

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/OpenBeta/EmailVerificationGateTest.php tests/Feature/Auth/EmailVerificationTest.php tests/Feature/Auth/VerificationNotificationTest.php tests/Feature/Auth/RegistrationTest.php tests/Feature/OpenBeta/OpenBetaRegistrationTest.php
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** Resend env configured; `npm run dev` if checking UI

### Happy path

1. Register a new account
2. Confirm redirect to **Email verification** (not beta pending yet)
3. Open verification link from Resend inbox (or mail log)
4. Confirm landing on **Beta application pending** (`?verified=1`)
5. After admin approve, confirm dashboard access

### Checks

- [ ] No console errors
- [ ] Resend verification email arrives with working link
- [ ] Profile shows resend banner if email is changed to a new unverified address
- [ ] Light and dark mode on verify-email page

### Regression

- [ ] Login/logout still works for already-verified users
- [ ] Pending beta applicants (verified) still see beta pending

## Suggested application commit

```
Summary: Re-enable email verification with Resend

Require MustVerifyEmail again now that outbound mail is configured.
New signups verify before beta pending or app access; document Resend env.
```

## Linear paste block

```
Title: Re-enable email verification with Resend

Description:
Email verification is required again. Registration no longer auto-verifies; users verify via Resend before open-beta pending and app routes. Profile resend banner restored.

Comment / instructions:
Set MAIL_MAILER=resend, RESEND_API_KEY, verified MAIL_FROM_ADDRESS. Queue worker if not sync. Visual QA: register → verify-email → Resend link → beta pending. Suggested: php artisan test --compact tests/Feature/OpenBeta/EmailVerificationGateTest.php tests/Feature/Auth/EmailVerificationTest.php tests/Feature/Auth/RegistrationTest.php
```
