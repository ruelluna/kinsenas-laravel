# Beta access codes for event auto-approval

**Date:** 2026-08-03

## Summary

Added optional beta access codes for physical events. Shared event codes (calling cards / QR links) and single-use batch codes auto-approve beta applications at registration. Email verification is still required before app access. Admins manage codes from a new **Beta access codes** screen.

## Changelog

- Register page accepts optional **Beta access code** field; `?beta_code=` query param pre-fills from QR/calling-card URLs
- Valid codes auto-approve at signup; invalid/expired/maxed codes block registration with a validation error
- No code → unchanged manual-review pending flow
- Admin **Beta access codes** — create shared event codes, generate single-use batches, export CSV, activate/deactivate
- Beta applications list shows **Source** (event code label, manual, or pending review)
- GHL: code redemptions tag `beta-code-redeemed` at signup; `beta-approved` syncs after email verification

## Files touched

### Backend
- `app/Enums/BetaAccessCodeType.php`
- `app/Models/BetaAccessCode.php`, `BetaAccessCodeBatch.php`
- `app/Models/User.php` — `beta_access_code_id` relationship
- `app/Services/Billing/BetaAccessCodeService.php`
- `app/Services/Billing/BetaApplicationService.php` — `applyWithOptionalCode()`, `approveViaCode()`
- `app/Actions/Fortify/CreateNewUser.php`
- `app/Providers/FortifyServiceProvider.php`
- `app/Listeners/GrantBetaLaunchDiscountOnVerified.php`
- `app/Support/Marketing/GhlTagCatalog.php`
- `app/Http/Controllers/Admin/AdminBetaAccessCodeController.php`
- `app/Http/Controllers/Admin/AdminBetaApplicationController.php`
- `app/Http/Requests/Admin/StoreBetaAccessCodeRequest.php`, `StoreBetaAccessCodeBatchRequest.php`, `UpdateBetaAccessCodeRequest.php`
- `routes/admin.php`
- Migrations: `beta_access_code_batches`, `beta_access_codes`, `users.beta_access_code_id`
- Factories: `BetaAccessCodeFactory`, `BetaAccessCodeBatchFactory`

### Frontend
- `resources/js/pages/auth/register.tsx`
- `resources/js/pages/admin/beta-access-codes/index.tsx`, `create.tsx`
- `resources/js/pages/admin/beta-applications/index.tsx`
- `resources/js/components/admin/admin-sidebar-nav.tsx`
- `resources/js/types/billing.ts`

### Tests
- `tests/Feature/OpenBeta/BetaAccessCodeTest.php`
- `tests/Feature/OpenBeta/OpenBetaRegistrationTest.php`

## Deploy steps

```bash
php artisan migrate
php artisan wayfinder:generate --no-interaction
vendor/bin/pint --dirty
```

No frontend build required unless deploying compiled assets (`npm run build`).

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/OpenBeta/BetaAccessCodeTest.php
php artisan test --compact tests/Feature/OpenBeta/OpenBetaRegistrationTest.php
php artisan test --compact tests/Feature/OpenBeta/EmailVerificationGateTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test

### Admin — create event code

1. Log in as platform admin
2. Open **Beta access codes** in the admin sidebar
3. Click **Create codes** → fill event code (e.g. `KINSENAS-MNL-2026`) and label
4. Confirm code appears in list with `0 used`

### Member — register with code

1. Open `http://financial-literacy.test/register?beta_code=KINSENAS-MNL-2026`
2. Confirm code field is pre-filled and alert mentions the event
3. Register → verify email → confirm dashboard access (not beta pending)

### Regression

1. Register without a code → verify email → beta pending page
2. Admin approve from **Beta applications** still works

## Suggested application commit

```
Summary: Add beta access codes for event auto-approval

Shared and single-use codes auto-approve open beta signup at registration while
keeping email verification as the app access gate. Admins can create codes,
export batches, and see application source in the beta review list.
```

## Linear paste block

```
Title: Beta access codes for event auto-approval

Description:
Optional beta access codes for physical events. Shared event codes on calling cards
or single-use batches auto-approve signup; email verification still required for app access.
Admin CRUD at Beta access codes with CSV export for print batches.

Comment / instructions:
Run php artisan migrate after deploy. Visual QA: admin create code → register with ?beta_code= → verify email → dashboard. Suggested: php artisan test --compact tests/Feature/OpenBeta/BetaAccessCodeTest.php
```
