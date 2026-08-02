# Registration email opt-in — breakdown

**Date:** 2026-08-03 01:09

## Summary

Added an optional, unchecked checkbox on registration for helpful product email (tips, updates, beta news). Consent is stored on `users` with an audit timestamp. Beta onboarding sequences in GHL remain separate; no GHL sync in this pass.

## Changelog

- Register page shows optional **Send me helpful emails from Kinsenas** (unchecked by default)
- `marketing_emails_opt_in` and `marketing_emails_opted_in_at` persisted on user create
- No change to GHL tags or beta lifecycle email workflows

## Files touched

### Database
- `database/migrations/2026_08_02_170943_add_marketing_emails_opt_in_to_users_table.php`

### Backend
- `app/Models/User.php`
- `app/Actions/Fortify/CreateNewUser.php`
- `database/factories/UserFactory.php`

### Frontend
- `resources/js/pages/auth/register.tsx`

### Tests
- `tests/Feature/Auth/RegistrationTest.php`
- `tests/Feature/OpenBeta/OpenBetaRegistrationTest.php`

## Deploy / verify

1. `php artisan migrate`
2. `npm run dev` or `npm run build` (register UI changed)
3. `vendor/bin/pint --dirty`

## Suggested tests (run manually)

```bash
php artisan migrate
php artisan test --compact tests/Feature/Auth/RegistrationTest.php
php artisan test --compact tests/Feature/OpenBeta/OpenBetaRegistrationTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test/register  
**Prereqs:** `npm run dev` (or `npm run build`)

### Happy path

1. Open **Register**
2. Confirm checkbox is **unchecked** with helper text about tips and updates
3. Register without checking — account works; DB `marketing_emails_opt_in = 0`
4. Register another user with box checked — DB `marketing_emails_opt_in = 1` and `marketing_emails_opted_in_at` set

### Checks

- [ ] No console errors
- [ ] Copy does not say “marketing emails”
- [ ] Open beta register flow unchanged aside from checkbox

## Suggested application commit

```
Summary: Add optional registration email opt-in checkbox

Persist product-update consent on users with an audit timestamp. Checkbox
is unchecked by default and uses non-promotional copy on the register form.
```

## Linear paste block

```
Title: Add optional registration email opt-in checkbox

Description:
Members can optionally opt in to helpful Kinsenas emails at registration. Consent is stored on the user record with opted_in_at for audit. Beta GHL sequences unchanged.

Comment / instructions:
Run php artisan migrate. npm run build for register page. Suggested: php artisan test --compact tests/Feature/Auth/RegistrationTest.php
```
