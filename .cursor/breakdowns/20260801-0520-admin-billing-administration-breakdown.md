# Admin Billing Administration — Implementation Breakdown

**Date:** 2026-08-01

## Summary

Expanded platform admin billing operations: subscriber lifecycle management, plan/price/feature CRUD, improved payment review, scheduled subscription status sync, platform admin user management, admin sidebar navigation, and subscription feature route gating.

## Changelog

- **Subscribers** — `/admin/subscribers` list with status/search filters; detail page with extend trial, manual activate, change plan, cancel
- **Plans** — create/edit plans with trial days, monthly/yearly prices (centavos), and feature checkboxes
- **Payments** — proof image preview, status filter, formatted amounts, required reject notes, pending-only approve/reject guards; members blocked from duplicate pending submissions
- **Platform admins** — `/admin/platform-users` grant/revoke with last-admin and self-revoke safeguards
- **Scheduler** — `billing:sync-subscription-status` daily marks expired trials/periods as past due
- **Feature gating** — `savings_plan`, `transfers`, `reports` enforced on savings routes via `subscribed.feature` middleware
- **Admin nav** — Admin section in sidebar when `isPlatformAdmin` is true
- **Platform admin bypass** — platform admins retain savings access for QA regardless of own subscription

## Files touched

**Backend:** `SubscriptionFeature` enum, billing factories, expanded `SubscriptionService`, sync command, admin controllers/requests, `EnsureSubscriptionFeature` middleware, `routes/admin.php`, `routes/savings.php`, `routes/console.php`, `HandleInertiaRequests`, `PaymentSubmissionController` guard

**Frontend:** `admin/subscribers/*`, `admin/plans/create|edit`, enhanced `plans/index`, `payment-submissions/index`, `admin/platform-users/index`, `admin-sidebar-nav`, `app-sidebar`, `types/billing.ts`

**Tests:** `SubscriptionPlanAdminTest`, `SubscriberAdminTest`, `PaymentSubmissionAdminTest`, `PlatformUserAdminTest`, `SyncSubscriptionStatusCommandTest`, `SubscriptionFeatureTest`

## Deploy steps

```bash
php artisan wayfinder:generate --no-interaction
vendor/bin/pint --dirty
npm run dev
```

No new migrations required.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Admin/SubscriptionPlanAdminTest.php
php artisan test --compact tests/Feature/Admin/SubscriberAdminTest.php
php artisan test --compact tests/Feature/Admin/PaymentSubmissionAdminTest.php
php artisan test --compact tests/Feature/Admin/PlatformUserAdminTest.php
php artisan test --compact tests/Feature/Billing/SyncSubscriptionStatusCommandTest.php
php artisan test --compact tests/Feature/Billing/SubscriptionFeatureTest.php
php artisan billing:sync-subscription-status
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`

### Happy path

1. Log in as **Platform Admin** (`admin@example.com` / factory password)
2. Confirm **Admin** section appears in sidebar: Subscribers, Plans, Payments, Payment QR, Platform admins
3. **Admin → Subscribers** — filter by status, open a member, extend trial by 7 days
4. **Admin → Plans** — create a plan with features and prices; edit Basic plan trial days
5. **Admin → Payments** — filter pending; approve one submission; reject another with notes and proof preview
6. **Admin → Platform admins** — grant admin to a user; confirm self-revoke and last-admin toggles are disabled
7. Log in as expired-trial member — confirm redirect to **Settings → Billing**
8. Log in as platform admin with expired trial — confirm savings pages still accessible

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Amounts show as ₱ formatted values on plans and payments pages
- [ ] Sidebar Admin section only visible for platform admins
- [ ] Light and dark mode on new admin pages

## Suggested commit

```
Summary: Add full platform admin billing administration

Operators can manage subscribers, plans, payments, and platform admins from
the sidebar. Includes scheduled past-due sync, feature gating on savings
routes, and improved PayMaya payment review with proof preview.
```

## Linear paste block

```
Title: Add full platform admin billing administration

Description:
Platform admins get subscriber directory with manual lifecycle ops (extend trial, activate, change plan, cancel), plan/price/feature CRUD, improved payment submission review, platform admin grant/revoke, daily past-due sync, and subscription feature gating on savings routes. Admin nav appears in sidebar for platform admins.

Comment / instructions:
Run wayfinder generate and npm run dev. Visual QA: log in as admin@example.com → Admin sidebar → subscribers, plans, payments, platform users. Suggested: php artisan test --compact tests/Feature/Admin/ and tests/Feature/Billing/.
```
