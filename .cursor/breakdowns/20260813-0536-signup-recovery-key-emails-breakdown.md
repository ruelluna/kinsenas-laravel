# Signup recovery key + admin notification emails

**Date:** 2026-08-13

## Summary

New signups (web and API) now email the member their vault **recovery key** with strong one-time safety warnings. Admins receive a separate signup alert (To `ruelluna@gmail.com`, CC `hello@kinsenas.ph`) with user details only — no recovery key.

## Changelog

- Recovery key emailed to new users on signup with warnings: only time we email it, store safely, do not delete the email
- Admin signup alert sent on every registration (web + API) without the recovery key
- Configurable admin recipients via `SIGNUP_ADMIN_NOTIFY_TO` / `SIGNUP_ADMIN_NOTIFY_CC`
- Dashboard recovery-key banner unchanged (redundant with email until session ends)

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| DEK vs recovery key | Email recovery key only | Raw DEK stays internal |
| Recipients | User gets key; admin gets signup alert without key | Admin To/CC from config |
| Marketing opt-in | Always send recovery key mail | Transactional security email |
| Dashboard banner | Retain | Email + in-app both show key |

## Files touched

**Config**
- `config/signup.php` (new)
- `.env.example`

**Backend**
- `app/Actions/Fortify/CreateNewUser.php`
- `app/Services/Vault/SignupVaultNotificationService.php` (new)
- `app/Notifications/Vault/RecoveryKeyIssued.php` (new)
- `app/Notifications/Admin/NewUserRegistered.php` (new)

**Tests**
- `tests/Feature/Notifications/SignupVaultNotificationTest.php` (new)
- `tests/Feature/Mail/BrandedMailTemplateTest.php`

## Deploy / verify

- Optional: set `SIGNUP_ADMIN_NOTIFY_TO` / `SIGNUP_ADMIN_NOTIFY_CC` in production `.env` (defaults match ruelluna@gmail.com / hello@kinsenas.ph)
- Ensure queue worker runs (`QUEUE_CONNECTION=database` in production) — notifications are queued with `afterCommit`
- Resend from address must be verified for production mail

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Notifications/SignupVaultNotificationTest.php
php artisan test --compact tests/Feature/Mail/BrandedMailTemplateTest.php
php artisan test --compact tests/Feature/Auth/RegistrationTest.php
php artisan test --compact tests/Feature/Api/V1/AuthTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

N/A — email-only change; dashboard recovery banner unchanged.

## Suggested commit

```
Summary: Email recovery key on signup and notify admins

New users receive their vault recovery key by email with one-time safety
warnings. Admins get a separate signup alert without the key (To/CC configurable).
```

## Linear paste block

```
Title: Email recovery key on signup and notify admins

Description:
New signups receive their vault recovery key by email with warnings that this is
the only time it is emailed. Admins are notified separately at ruelluna@gmail.com
(CC hello@kinsenas.ph) with signup details only — no recovery key.

Comment / instructions:
Ensure queue worker is running in production. Optional env: SIGNUP_ADMIN_NOTIFY_TO,
SIGNUP_ADMIN_NOTIFY_CC. Suggested: php artisan test --compact tests/Feature/Notifications/SignupVaultNotificationTest.php
```
