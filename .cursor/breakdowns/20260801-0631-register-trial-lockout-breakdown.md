# Register trial info and billing-only lockout

**Date:** 2026-08-01

## Summary

Registration now shows the default plan’s free trial and post-trial pricing. When a trial or subscription period expires, members are locked to billing pages only—middleware, login redirects, sidebar, and settings nav all enforce this. Platform admins are unchanged.

## Changelog

- Register page displays trial days, plan name, and post-trial prices before signup
- Fortify register view passes `passwordRules` and `trialOffer` props
- `BillingPlanPresenter` centralizes plan/trial offer formatting for register and billing
- Expired members redirect to **Settings → Billing** from dashboard, profile, vault unlock, and login
- Sidebar and settings nav show **Billing** only when subscription access is denied
- Billing page shows locked-out and active-trial alerts
- Shared Inertia `subscription` prop includes `hasAccess`, `statusLabel`, and `daysRemaining`

## Files touched

### Backend
- `app/Services/Billing/BillingPlanPresenter.php` (new)
- `app/Providers/FortifyServiceProvider.php`
- `app/Http/Controllers/Settings/BillingController.php`
- `app/Http/Middleware/EnsureSubscribedOrTrialing.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Responses/Concerns/RedirectsToCurrentTeam.php`
- `routes/settings.php`
- `routes/web.php`

### Frontend
- `resources/js/pages/auth/register.tsx`
- `resources/js/pages/settings/billing.tsx`
- `resources/js/components/app-sidebar.tsx`
- `resources/js/layouts/settings/layout.tsx`
- `resources/js/types/billing.ts`
- `resources/js/types/global.d.ts`
- `resources/js/types/index.ts`

### Tests
- `tests/Feature/Auth/RegistrationTest.php`
- `tests/Feature/Billing/TrialExpiredAccessTest.php` (new)

## Deploy / verify

- No migrations
- If frontend changed: `npm run dev` or `npm run build`

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Auth/RegistrationTest.php
php artisan test --compact tests/Feature/Billing/TrialExpiredAccessTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test

### Register trial info

1. Open **Register** (logged out)
2. Confirm trial card shows plan name, trial days, and monthly/yearly prices
3. Register a new account — confirm redirect to dashboard and trialing subscription

### Billing-only lockout

1. Log in as a member whose trial has expired (or set `trial_ends_at` to yesterday in admin)
2. Confirm login lands on **Settings → Billing**
3. Confirm sidebar shows **Billing** only
4. Confirm **Settings** sidebar shows **Billing** only
5. Try dashboard URL — confirm redirect back to billing
6. Submit payment proof from billing — page still loads

### Checks

- [ ] No console errors on register and billing pages
- [ ] Active trial users still see full sidebar and settings nav
- [ ] Platform admin with expired trial still has full access

## Suggested commit

```
Summary: Add register trial messaging and billing-only lockout

Show trial plan/pricing on signup and block expired members from app routes
except billing. Login and nav now send locked-out users straight to billing.
```
